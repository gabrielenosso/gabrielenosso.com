## Manage Paintings
Add a new .md file in `src/content/art/`.
In the .md file, `heroImage` is the main image, and `galleryImages` are additional images.
Paintings are shown in alphabetical order.

Run script to resize and rename extension of canvases pictures with:
```python
python optimize_images.py
```

Copy paste the end of the logs and put the list of images in the .md file.


## Development

```bash
# Install dependencies
npm i
# Start the development server
npm run dev
```


## Build & Deploy
```bash
npm run publish
```

#### Only build:
```bash
npm run build
```

Then for local testing:
```bash
npx serve ./dist
```
#### Only deploy:
```bash
npm run deploy
```


## Deployment
Copy the file example.env to .env and fill in your FTP credentials.

Then run:
```bash
npm run deploy
```


## Colors Pictures

To crop transparent backgrounds from PNG images in the colors folder:

```bash
# Install Pillow library (one time)
pip install Pillow

# Run the crop script
python crop_transparent.py
```

This will automatically process all PNG files in `public/colors/` and remove transparent padding.


## Credits
This project was builds on top of [void-astro](https://github.com/eAntillon/void-astro) by Erick Antillón(s)