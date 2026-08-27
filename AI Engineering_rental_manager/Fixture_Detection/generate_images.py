"""
generate_images.py
-------------------
Creates a placeholder image dataset for fixture issue detection,
matching FR-4.2: "System shall use AI to detect the issue type and
assign a priority level."

Categories: Plumbing, Electrical, Structural, Other
"""

import os
import numpy as np
from PIL import Image, ImageDraw

CATEGORIES = ["Plumbing", "Electrical", "Structural", "Other"]
IMAGES_PER_CATEGORY = 40
IMG_SIZE = 224

OUTPUT_DIR = "fixture_dataset"

CATEGORY_STYLE = {
    "Plumbing":   {"color": (70, 130, 180), "shape": "ellipse"},
    "Electrical": {"color": (230, 200, 40), "shape": "zigzag"},
    "Structural": {"color": (120, 120, 120), "shape": "crack"},
    "Other":      {"color": (150, 90, 200), "shape": "rectangle"},
}

np.random.seed(42)


def make_image(category):
    style = CATEGORY_STYLE[category]
    base_color = np.array(style["color"])

    noise = np.random.randint(-30, 30, (IMG_SIZE, IMG_SIZE, 3))
    img_array = np.clip(base_color + noise, 0, 255).astype(np.uint8)
    img = Image.fromarray(img_array)
    draw = ImageDraw.Draw(img)

    if style["shape"] == "ellipse":
        x0, y0 = np.random.randint(20, 60, 2)
        x1, y1 = x0 + np.random.randint(80, 140), y0 + np.random.randint(30, 60)
        draw.ellipse([x0, y0, x1, y1], outline=(255, 255, 255), width=4)
    elif style["shape"] == "zigzag":
        points = [(np.random.randint(0, IMG_SIZE), np.random.randint(0, IMG_SIZE)) for _ in range(6)]
        draw.line(points, fill=(255, 255, 255), width=4)
    elif style["shape"] == "crack":
        x, y = np.random.randint(20, 100, 2)
        for _ in range(5):
            nx, ny = x + np.random.randint(-30, 30), y + np.random.randint(20, 40)
            draw.line([x, y, nx, ny], fill=(20, 20, 20), width=3)
            x, y = nx, ny
    else:
        x0, y0 = np.random.randint(30, 70, 2)
        x1, y1 = x0 + np.random.randint(60, 120), y0 + np.random.randint(60, 120)
        draw.rectangle([x0, y0, x1, y1], outline=(255, 255, 255), width=4)

    return img


def main():
    for category in CATEGORIES:
        folder = os.path.join(OUTPUT_DIR, category)
        os.makedirs(folder, exist_ok=True)
        for i in range(IMAGES_PER_CATEGORY):
            img = make_image(category)
            img.save(os.path.join(folder, f"{category.lower()}_{i:03d}.jpg"))
        print(f"Created {IMAGES_PER_CATEGORY} images in {folder}")

    print(f"\nDone. Dataset saved in ./{OUTPUT_DIR}/")


if __name__ == "__main__":
    main()