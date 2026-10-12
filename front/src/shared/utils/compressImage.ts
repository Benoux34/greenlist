const MAX_SIDE = 1600;
const MAX_BYTES = 900_000;

async function compressImage(file: Blob): Promise<Blob> {
  const url = URL.createObjectURL(file);

  try {
    const image = new Image();
    image.src = url;
    await image.decode();

    const scale = Math.min(
      1,
      MAX_SIDE / Math.max(image.naturalWidth, image.naturalHeight),
    );
    const canvas = document.createElement("canvas");
    canvas.width = Math.round(image.naturalWidth * scale);
    canvas.height = Math.round(image.naturalHeight * scale);

    const context = canvas.getContext("2d");
    if (context === null) throw new Error("No 2D canvas available.");

    context.fillStyle = "#fff";
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.drawImage(image, 0, 0, canvas.width, canvas.height);

    for (const quality of [0.8, 0.6, 0.4, 0.2]) {
      const blob = await new Promise<Blob | null>((resolve) =>
        canvas.toBlob(resolve, "image/jpeg", quality),
      );
      if (blob !== null && blob.size <= MAX_BYTES) return blob;
    }

    throw new Error("The image is still too large once compressed.");
  } finally {
    URL.revokeObjectURL(url);
  }
}

export { compressImage };
