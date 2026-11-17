/**
 * Generic harness runner.
 *
 *   node run.mjs php/02-doctor.php
 *   node run.mjs /abs/path/to/script.php
 *
 * Mounts /tmp/wpsite at /wordpress and the repo's mysql/ directory at /import,
 * then executes the given PHP file inside real PHP 8.3 (php-wasm).
 *
 * Any `require __DIR__ . '/sibling.php'` in the script is resolved on the host
 * and re-pointed at /harness/, because the script body is evaluated from
 * /internal inside the wasm filesystem.
 */
import { PHP } from '@php-wasm/universal';
import { loadNodeRuntime, createNodeFsMountHandler } from '@php-wasm/node';
import { readFileSync, existsSync } from 'node:fs';
import { basename, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const HERE = dirname(fileURLToPath(import.meta.url));
const REPO = process.env.DRPLUS_REPO || '/home/user/drpus-manage-rooms';
const SITE = process.env.WPSITE || '/tmp/wpsite';

const arg = process.argv[2];
if (!arg) {
  console.error('usage: node run.mjs <php-script>');
  process.exit(2);
}
const scriptPath = arg.startsWith('/') ? arg : resolve(HERE, arg);
if (!existsSync(scriptPath)) {
  console.error(`script not found: ${scriptPath}`);
  process.exit(2);
}

const source = readFileSync(scriptPath, 'utf8');
const deps = {};
for (const m of source.matchAll(/require(?:_once)?\s+__DIR__\s*\.\s*'([^']+)'/g)) {
  const host = resolve(dirname(scriptPath), m[1].replace(/^\/+/, ''));
  deps[m[1].replace(/^\.?\//, '')] = readFileSync(host, 'utf8');
}

const runtime = await loadNodeRuntime('8.3', {
  emscriptenOptions: { processId: 4100 + Math.floor(Math.random() * 400) },
});
const php = new PHP(runtime, 'harness');
await php.mkdir('/wordpress');
await php.mkdir('/import');
await php.mkdir('/harness');
await php.mount('/wordpress', createNodeFsMountHandler(SITE));
await php.mount('/import', createNodeFsMountHandler(resolve(REPO, 'mysql')));
await php.mount('/repo', createNodeFsMountHandler(REPO));

for (const [name, code] of Object.entries(deps)) {
  await php.writeFile(`/harness/${name}`, code);
}

const body = source
  .replace(/require(?:_once)?\s+__DIR__\s*\.\s*'([^']+)'/g, (_, rel) => `require '/harness/${rel.replace(/^\.?\//, '')}'`)
  .replace(/^<\?php/, '');

const debug = process.env.HARNESS_DEBUG === '1' ? "define('HARNESS_DEBUG', true);" : '';
try {
  const r = await php.run({ code: `<?php ${debug} ${body}` });
  process.stdout.write(r.text || '');
  if (r.errors) process.stdout.write(`\n[STDERR] ${r.errors.slice(0, 4000)}\n`);
} catch (e) {
  const resp = e.response;
  if (resp && resp.bytes) process.stdout.write(Buffer.from(resp.bytes).toString().slice(0, 8000));
  else process.stdout.write(String(e).slice(0, 4000));
  process.stdout.write(`\n[EXIT CODE] ${resp ? resp.exitCode : '?'}\n`);
  process.exitCode = 1;
}
process.exit(0);
