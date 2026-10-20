import {buildGeneral} from 'espo-extension-tools';
import fs from 'fs';

const moduleVendor = 'build/tmp/files/custom/Espo/Modules/GuardCore/vendor/rennf93';

// Only the sources run in EspoCRM: the bundled libraries ship docs, tests, conformance fixtures and examples too.
const keep = new Set(['src', 'composer.json', 'LICENSE']);

const pruneBundledLibraries = () => {
    for (const library of fs.readdirSync(moduleVendor)) {
        for (const entry of fs.readdirSync(`${moduleVendor}/${library}`)) {
            if (!keep.has(entry)) {
                fs.rmSync(`${moduleVendor}/${library}/${entry}`, {recursive: true, force: true});
            }
        }
    }
};

buildGeneral({extensionHook: pruneBundledLibraries});
