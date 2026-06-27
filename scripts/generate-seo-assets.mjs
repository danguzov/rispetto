import fs from 'node:fs';
import path from 'node:path';
import sharp from 'sharp';

const publicDir = path.resolve('public');
const fontPath = path.resolve('public/fonts/benguiat/BenguiatStd-Bold.woff2');
const fontBase64 = fs.readFileSync(fontPath).toString('base64');

const brandGreen = '#00ff03';
const brandBg = '#050505';

const faviconSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" role="img" aria-label="R">
  <defs>
    <style>
      @font-face {
        font-family: 'Benguiat';
        src: url('data:font/woff2;base64,${fontBase64}') format('woff2');
        font-weight: 700;
        font-style: normal;
      }
    </style>
  </defs>
  <rect width="64" height="64" fill="${brandBg}"/>
  <text
    x="32"
    y="46"
    text-anchor="middle"
    font-family="Benguiat, Georgia, 'Times New Roman', serif"
    font-size="46"
    font-weight="700"
    fill="${brandGreen}"
  >R</text>
</svg>`;

fs.writeFileSync(path.resolve(publicDir, 'favicon.svg'), faviconSvg);

const sizes = [
    { name: 'favicon-16.png', size: 16 },
    { name: 'favicon-32.png', size: 32 },
    { name: 'favicon-48.png', size: 48 },
    { name: 'apple-touch-icon.png', size: 180 },
    { name: 'icon-192.png', size: 192 },
    { name: 'icon-512.png', size: 512 },
];

for (const { name, size } of sizes) {
    await sharp(Buffer.from(faviconSvg))
        .resize(size, size)
        .png()
        .toFile(path.resolve(publicDir, name));
}

await sharp(Buffer.from(faviconSvg))
    .resize(48, 48)
    .png()
    .toFile(path.resolve(publicDir, 'favicon.ico'));

console.log('SEO assets generated from green R favicon.svg');
