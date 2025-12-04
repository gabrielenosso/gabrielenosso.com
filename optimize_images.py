#!/usr/bin/env python3
"""
Image optimization script for art portfolio.
Resizes images to web-friendly dimensions while preserving quality for art details.
Also normalizes extensions to lowercase .jpg
"""

from pathlib import Path
from PIL import Image
import os

# =============================================================================
# CONFIGURATION
# =============================================================================

# Target maximum dimensions (longest edge)
MAX_LONG_EDGE = 2400

# JPEG quality (1-100). 85-90 is good for art with fine details
JPEG_QUALITY = 88

# Folder to process
CANVASES_FOLDER = Path(__file__).parent / "public" / "canvases"

# Dry run mode - set to True to only print what would be done without changing files
DRY_RUN = False

# =============================================================================
# SCRIPT
# =============================================================================

def get_all_images(folder: Path) -> list[Path]:
    """Recursively find all image files in folder."""
    extensions = {'.jpg', '.jpeg', '.png', '.webp', '.JPG', '.JPEG', '.PNG', '.WEBP'}
    images = []
    for ext in extensions:
        images.extend(folder.rglob(f"*{ext}"))
    return sorted(set(images))


def needs_resize(img: Image.Image) -> bool:
    """Check if image exceeds maximum dimensions."""
    width, height = img.size
    return max(width, height) > MAX_LONG_EDGE


def calculate_new_size(width: int, height: int) -> tuple[int, int]:
    """Calculate new dimensions maintaining aspect ratio."""
    if width >= height:
        # Landscape or square
        new_width = MAX_LONG_EDGE
        new_height = int(height * (MAX_LONG_EDGE / width))
    else:
        # Portrait
        new_height = MAX_LONG_EDGE
        new_width = int(width * (MAX_LONG_EDGE / height))
    return new_width, new_height


def needs_rename(path: Path) -> bool:
    """Check if file extension needs to be normalized to lowercase .jpg"""
    return path.suffix.lower() in {'.jpg', '.jpeg'} and path.suffix != '.jpg'


def optimize_image(path: Path) -> Path:
    """Resize image if needed and save with proper extension. Returns final path."""
    
    # Open image
    img = Image.open(path)
    original_size = img.size
    original_mode = img.mode
    
    # Convert to RGB if necessary (e.g., RGBA PNGs)
    if img.mode in ('RGBA', 'P'):
        img = img.convert('RGB')
    
    # Check if resize needed
    resized = False
    if needs_resize(img):
        new_size = calculate_new_size(*original_size)
        img = img.resize(new_size, Image.LANCZOS)
        resized = True
    
    # Determine new path (normalize extension to lowercase .jpg)
    new_path = path.with_suffix('.jpg')
    renamed = path.suffix != '.jpg'
    
    # Build status string
    status = []
    if resized:
        status.append(f"{original_size[0]}x{original_size[1]} -> {img.size[0]}x{img.size[1]}")
    if renamed:
        status.append(f"{path.suffix} -> .jpg")
    
    # Save if any changes needed
    if resized or renamed or original_mode != 'RGB':
        if DRY_RUN:
            print(f"  [DRY RUN] {path.name} ({', '.join(status) if status else 'convert'})")
        else:
            if renamed:
                temp_path = path.with_suffix('.tmp.jpg')
                img.save(temp_path, 'JPEG', quality=JPEG_QUALITY, optimize=True, progressive=True)
                img.close()
                path.unlink()
                temp_path.rename(new_path)
            else:
                img.save(new_path, 'JPEG', quality=JPEG_QUALITY, optimize=True, progressive=True)
            print(f"  ✓ {new_path.name}" + (f" ({', '.join(status)})" if status else ""))
    else:
        print(f"  · {new_path.name} (no changes)")
    
    img.close()
    return new_path


def main():
    print("=" * 60)
    print("Image Optimization Script for Art Portfolio")
    print("=" * 60)
    print(f"\nSettings:")
    print(f"  Max long edge: {MAX_LONG_EDGE}px")
    print(f"  JPEG quality:  {JPEG_QUALITY}")
    print(f"  Folder:        {CANVASES_FOLDER}")
    print(f"  Dry run:       {DRY_RUN}")
    
    if not CANVASES_FOLDER.exists():
        print(f"\nError: Folder not found: {CANVASES_FOLDER}")
        return
    
    images = get_all_images(CANVASES_FOLDER)
    print(f"Found {len(images)} image(s)\n")
    
    if not images:
        print("No images to process.")
        return
    
    # Group images by subfolder
    folders: dict[Path, list[Path]] = {}
    for img_path in images:
        folder = img_path.parent
        if folder not in folders:
            folders[folder] = []
        folders[folder].append(img_path)
    
    # Process each folder and collect final paths
    all_results: dict[Path, list[Path]] = {}
    
    for folder, folder_images in sorted(folders.items()):
        folder_name = folder.relative_to(CANVASES_FOLDER)
        print(f"[{folder_name}]")
        
        final_paths = []
        for img_path in folder_images:
            try:
                final_path = optimize_image(img_path)
                final_paths.append(final_path)
            except Exception as e:
                print(f"  ✗ {img_path.name}: {e}")
        
        all_results[folder] = final_paths
        print()
    
    # Print YAML snippet for each folder
    print("=" * 60)
    print("Copy-paste for your .md files:")
    print("=" * 60)
    
    for folder, final_paths in sorted(all_results.items()):
        if not final_paths:
            continue
        
        folder_rel = folder.relative_to(CANVASES_FOLDER.parent)  # relative to public/
        print(f"\n# {folder_rel}")
        
        # First image as hero
        hero = final_paths[0]
        hero_path = "/" + str(hero.relative_to(CANVASES_FOLDER.parent)).replace("\\", "/")
        print(f'heroImage: "{hero_path}"')
        
        # Rest as gallery (if more than 1)
        if len(final_paths) > 1:
            print("galleryImages:")
            for p in final_paths[1:]:
                img_path = "/" + str(p.relative_to(CANVASES_FOLDER.parent)).replace("\\", "/")
                print(f'  - "{img_path}"')
    
    print()


if __name__ == "__main__":
    main()
