import { existsSync } from 'node:fs';
import { execFileSync } from 'node:child_process';

const ROOT = process.cwd();

function isGitRepository() {
  return existsSync(`${ROOT}\\.git`) || existsSync(`${ROOT}/.git`);
}

if (!isGitRepository()) {
  console.log('[hooks] Repositorio git no detectado. Se omite la instalación del hook.');
  process.exit(0);
}

try {
  execFileSync('git', ['config', 'core.hooksPath', '.githooks'], {
    cwd: ROOT,
    stdio: 'ignore',
  });

  console.log('[hooks] core.hooksPath configurado en .githooks');
} catch (error) {
  console.warn('[hooks] No se ha podido configurar core.hooksPath automáticamente.');
  console.warn('[hooks] Ejecuta manualmente: git config core.hooksPath .githooks');
}
