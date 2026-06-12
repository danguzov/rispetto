import { readdir } from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';

const sourceDir = path.resolve('public/images/site');
const widths = [640, 1024, 1280];

async function optimizeImage(filePath) {
    const parsed = path.parse(filePath);

    await sharp(filePath)
        .rotate()
        .webp({ quality: 82 })
        .toFile(path.join(parsed.dir, `${parsed.name}.webp`));

    for (const width of widths) {
        const resized = sharp(filePath).rotate().resize({ width, withoutEnlargement: true });

        await resized
            .clone()
            .toFile(path.join(parsed.dir, `${parsed.name}-${width}w${parsed.ext}`));

        await resized
            .clone()
            .webp({ quality: 82 })
            .toFile(path.join(parsed.dir, `${parsed.name}-${width}w.webp`));
    }

    console.log(`Optimized: ${path.basename(filePath)}`);
}

async function run() {
    let entries;

    try {
        entries = await readdir(sourceDir);
    } catch {
        console.log('No public/images/site directory found. Create it and add source images first.');

        return;
    }

    const images = entries.filter((file) => /\.(jpe?g|png)$/i.test(file) && !/-\d+w\./i.test(file));

    if (images.length === 0) {
        console.log('No JPEG/PNG source images found in public/images/site.');

        return;
    }

    for (const image of images) {
        await optimizeImage(path.join(sourceDir, image));
    }
}

run();
