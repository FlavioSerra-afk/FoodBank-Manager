import path from 'path';
import { pathToFileURL } from 'url';

const harnessDir = path.resolve(__dirname, '..', 'harness');

export function getHarnessUrl(file: string): string {
  return pathToFileURL(path.join(harnessDir, file)).toString();
}
