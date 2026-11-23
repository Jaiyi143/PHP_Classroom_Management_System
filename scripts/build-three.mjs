import { copyFile, mkdir } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const source = join(root, 'node_modules', 'three', 'build');
const destination = join(root, 'assets', 'vendor', 'three');

await mkdir(destination, { recursive: true });
for (const file of ['three.core.js', 'three.module.js']) {
  await copyFile(join(source, file), join(destination, file));
}
await copyFile(join(root, 'node_modules', 'three', 'LICENSE'), join(destination, 'LICENSE'));
console.log('Three.js browser modules copied to assets/vendor/three');
