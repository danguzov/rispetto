import path from 'node:path';
import sharp from 'sharp';

const favicon = path.resolve('public/apple-touch-icon.png');

await sharp(favicon)
    .resize(192, 192)
    .png()
    .toFile(path.resolve('public/icon-192.png'));

await sharp(favicon)
    .resize(512, 512)
    .png()
    .toFile(path.resolve('public/icon-512.png'));

console.log('SEO assets generated: icon-192.png, icon-512.png');
