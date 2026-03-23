import { promises as fs, watch } from 'node:fs';
import path from 'node:path';

const ROOT = process.cwd();
const watchedRoots = [
  'style.css',
  'public/assets/css',
  'public/assets/js',
  'admin/assets/css',
];

const extensions = new Set(['.css', '.js']);
let buildTimeout = null;

async function pathExists(targetPath) {
  try {
    await fs.access(targetPath);
    return true;
  } catch {
    return false;
  }
}

async function collectFiles(entry) {
  const absoluteEntry = path.join(ROOT, entry);

  if (!(await pathExists(absoluteEntry))) {
    return [];
  }

  const stat = await fs.stat(absoluteEntry);

  if (stat.isFile()) {
    return [absoluteEntry];
  }

  const collected = [];
  const queue = [absoluteEntry];

  while (queue.length > 0) {
    const currentDir = queue.pop();
    const entries = await fs.readdir(currentDir, { withFileTypes: true });

    for (const currentEntry of entries) {
      const fullPath = path.join(currentDir, currentEntry.name);

      if (currentEntry.isDirectory()) {
        queue.push(fullPath);
        continue;
      }

      if (!currentEntry.isFile()) {
        continue;
      }

      if (!extensions.has(path.extname(currentEntry.name))) {
        continue;
      }

      if (currentEntry.name.includes('.min.')) {
        continue;
      }

      collected.push(fullPath);
    }
  }

  return collected.sort();
}

function stripBlockComments(input) {
  return input.replace(/\/\*[\s\S]*?\*\//g, '');
}

function minifyCssContent(input) {
  return stripBlockComments(input)
    .replace(/\s+/g, ' ')
    .replace(/\s*([{}:;,>])\s*/g, '$1')
    .replace(/;}/g, '}')
    .trim();
}

function minifyJavascriptContent(input) {
  return stripBlockComments(input)
    .replace(/\n\s*\n/g, '\n')
    .replace(/[ \t]+\n/g, '\n')
    .trim();
}

async function writeMinifiedAsset(filePath) {
  const extension = path.extname(filePath);
  const minPath = filePath.replace(new RegExp(`${extension}$`), `.min${extension}`);
  const source = await fs.readFile(filePath, 'utf8');
  const output = extension === '.css'
    ? minifyCssContent(source)
    : minifyJavascriptContent(source);

  await fs.writeFile(minPath, output, 'utf8');

  return path.relative(ROOT, minPath);
}

async function buildAllAssets() {
  const sourceFiles = new Set();

  for (const entry of watchedRoots) {
    const files = await collectFiles(entry);
    for (const file of files) {
      sourceFiles.add(file);
    }
  }

  if (sourceFiles.size === 0) {
    console.warn('No se han encontrado assets para minificar.');
    return;
  }

  const writtenFiles = [];
  for (const file of [...sourceFiles].sort()) {
    const writtenFile = await writeMinifiedAsset(file);
    writtenFiles.push(writtenFile);
  }

  console.log(`Assets minificados: ${writtenFiles.length}`);
  for (const writtenFile of writtenFiles) {
    console.log(`- ${writtenFile}`);
  }
}

function shouldIgnoreFile(filename = '') {
  return filename.includes('.min.') || !extensions.has(path.extname(filename));
}

function queueBuild(reason) {
  if (buildTimeout) {
    clearTimeout(buildTimeout);
  }

  buildTimeout = setTimeout(async () => {
    console.log(`\n[watch] Cambio detectado: ${reason}`);
    try {
      await buildAllAssets();
    } catch (error) {
      console.error(error);
    }
  }, 150);
}

async function startWatcher() {
  await buildAllAssets();

  for (const entry of watchedRoots) {
    const absoluteEntry = path.join(ROOT, entry);

    if (!(await pathExists(absoluteEntry))) {
      continue;
    }

    const stat = await fs.stat(absoluteEntry);
    const watchTarget = stat.isDirectory() ? absoluteEntry : path.dirname(absoluteEntry);

    watch(watchTarget, { recursive: stat.isDirectory() }, (eventType, filename) => {
      if (!filename || shouldIgnoreFile(filename)) {
        return;
      }

      queueBuild(`${eventType} -> ${filename}`);
    });
  }

  console.log('\n[watch] Observando cambios en assets. Pulsa Ctrl+C para salir.');
}

const mode = process.argv[2] ?? 'build';

if (mode === 'watch') {
  startWatcher().catch((error) => {
    console.error(error);
    process.exitCode = 1;
  });
} else {
  buildAllAssets().catch((error) => {
    console.error(error);
    process.exitCode = 1;
  });
}
