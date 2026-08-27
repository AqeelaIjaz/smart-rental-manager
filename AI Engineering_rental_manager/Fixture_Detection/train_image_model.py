"""
train_image_model.py
----------------------
Trains an image classifier for fixture issue detection using TRANSFER
LEARNING: we reuse MobileNetV2 (already trained on millions of general
images) and only retrain its final layers to recognize our 4 categories.

Implements FR-4.2: "System shall use AI to detect the issue type and
assign a priority level."
"""

import tensorflow as tf
from tensorflow.keras import layers, models
from tensorflow.keras.applications import MobileNetV2
from tensorflow.keras.preprocessing.image import ImageDataGenerator

IMG_SIZE = 224
BATCH_SIZE = 8
DATASET_DIR = "fixture_dataset"
CATEGORIES = ["Plumbing", "Electrical", "Structural", "Other"]

CATEGORY_PRIORITY = {
    "Electrical": "High",
    "Structural": "High",
    "Plumbing":   "Medium",
    "Other":      "Low",
}

datagen = ImageDataGenerator(
    rescale=1.0 / 255,
    validation_split=0.2,
)

train_generator = datagen.flow_from_directory(
    DATASET_DIR,
    target_size=(IMG_SIZE, IMG_SIZE),
    batch_size=BATCH_SIZE,
    class_mode="categorical",
    subset="training",
    classes=CATEGORIES,
)

val_generator = datagen.flow_from_directory(
    DATASET_DIR,
    target_size=(IMG_SIZE, IMG_SIZE),
    batch_size=BATCH_SIZE,
    class_mode="categorical",
    subset="validation",
    classes=CATEGORIES,
)

print("Class label mapping:", train_generator.class_indices)

base_model = MobileNetV2(
    input_shape=(IMG_SIZE, IMG_SIZE, 3),
    include_top=False,
    weights="imagenet",
)
base_model.trainable = False

model = models.Sequential([
    base_model,
    layers.GlobalAveragePooling2D(),
    layers.Dense(64, activation="relu"),
    layers.Dropout(0.3),
    layers.Dense(len(CATEGORIES), activation="softmax"),
])

model.compile(
    optimizer="adam",
    loss="categorical_crossentropy",
    metrics=["accuracy"],
)

model.summary()

EPOCHS = 10
history = model.fit(
    train_generator,
    validation_data=val_generator,
    epochs=EPOCHS,
)

final_train_acc = history.history["accuracy"][-1]
final_val_acc = history.history["val_accuracy"][-1]
print(f"\nFinal training accuracy: {final_train_acc:.2%}")
print(f"Final validation accuracy: {final_val_acc:.2%}")

model.save("fixture_model.keras")
print("\nModel saved to fixture_model.keras")

import json
with open("category_priority.json", "w") as f:
    json.dump(CATEGORY_PRIORITY, f, indent=2)
print("Priority mapping saved to category_priority.json")