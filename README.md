## Development

```bash
# Install dependencies
npm i
# Start the development server
npm run dev
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