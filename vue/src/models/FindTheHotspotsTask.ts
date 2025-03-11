import { z } from 'zod';
import { imageFileSchema } from '@/models/common';

const rectangleHotspotSchema = z.object({
  uuid: z.string(),
  type: z.literal('rectangle'),
  x: z.number(), // Fraction 0..1 of image width
  y: z.number(), // Fraction 0..1 of image height
  width: z.number(), // Fraction 0..1 of image width
  height: z.number(), // Fraction 0..1 of image height
  correct: z.boolean().default(true),
});
export type RectangleHotspot = z.infer<typeof rectangleHotspotSchema>;

const ellipseHotspotSchema = z.object({
  uuid: z.string(),
  type: z.literal('ellipse'),
  x: z.number(), // Fraction 0..1 of image width
  y: z.number(), // Fraction 0..1 of image height
  width: z.number(), // Fraction 0..1 of image width
  height: z.number(), // Fraction 0..1 of image height
  correct: z.boolean().default(true),
});
export type EllipseHotspot = z.infer<typeof ellipseHotspotSchema>;

export const hotspotSchema = z.union([
  rectangleHotspotSchema,
  ellipseHotspotSchema,
]);
export type Hotspot = z.infer<typeof hotspotSchema>;
export type HotspotType = Hotspot['type'];

export const findTheHotspotsTaskSchema = z.object({
  task_type: z.literal('FindTheHotspots'),
  image: imageFileSchema,
  hotspots: z.array(hotspotSchema),
});
export type FindTheHotspotsTask = z.infer<typeof findTheHotspotsTaskSchema>;
