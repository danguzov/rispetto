import path from 'node:path';
import sharp from 'sharp';

const hero = path.resolve('public/images/site/hero.jpg');
const favicon = path.resolve('public/apple-touch-icon.png');

await sharp(hero)
    .resize(1200, 630, { fit: 'cover', position: 'centre' })
    .jpeg({ quality: 85, mozjpeg: true })
    .toFile(path.resolve('public/images/site/og-image.jpg'));

await sharp(favicon)
    .resize(192, 192)
    .png()
    .toFile(path.resolve('public/icon-192.png'));

await sharp(favicon)
    .resize(512, 512)
    .png()
    .toFile(path.resolve('public/icon-512.png'));

console.log('SEO assets generated: og-image.jpg, icon-192.png, icon-512.png');
