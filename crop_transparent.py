#!/usr/bin/env python3
"""
Crop transparent background from PNG images in the colors folder.
This script processes all PNG files in public/colors/ and removes transparent padding.
"""

from PIL import Image
import os
from pathlib import Path


def crop_transparent_background(image_path, output_path=None, tolerance=5):
    """
    Crop transparent background from a PNG image.
    
    Args:
        image_path: Path to the input PNG file
        output_path: Path to save the cropped image (if None, overwrites original)
        tolerance: Alpha threshold (0-255) - pixels with alpha <= tolerance are considered transparent
    """
    # Open the image
    img = Image.open(image_path)
    
    # Convert to RGBA if not already
    if img.mode != 'RGBA':
        img = img.convert('RGBA')
    
    # Get alpha channel
    alpha = img.split()[-1]
    
    # Create a binary mask where pixels with alpha > tolerance are kept
    from PIL import ImageChops
    threshold = ImageChops.constant(alpha, tolerance)
    mask = ImageChops.subtract(alpha, threshold)
    
    # Get the bounding box of non-transparent pixels
    bbox = mask.getbbox()
    
    if bbox:
        # Crop the image to the bounding box
        cropped = img.crop(bbox)
        
        # Save the result
        if output_path is None:
            output_path = image_path
        
        cropped.save(output_path, 'PNG')
        print(f"✓ Cropped {os.path.basename(image_path)}")
        print(f"  Original size: {img.size}")
        print(f"  Cropped size: {cropped.size}")
        return True
    else:
        print(f"✗ {os.path.basename(image_path)} is completely transparent")
        return False


def main():
    # Define the colors directory
    colors_dir = Path(__file__).parent / 'public' / 'colors'
    
    if not colors_dir.exists():
        print(f"Error: Directory {colors_dir} does not exist")
        return
    
    # Find all PNG files
    png_files = list(colors_dir.glob('*.png'))
    
    if not png_files:
        print(f"No PNG files found in {colors_dir}")
        return
    
    print(f"Found {len(png_files)} PNG file(s) in {colors_dir}\n")
    
    # Process each PNG file
    success_count = 0
    for png_file in png_files:
        if crop_transparent_background(png_file):
            success_count += 1
        print()
    
    print(f"Successfully processed {success_count}/{len(png_files)} files")


if __name__ == '__main__':
    main()
