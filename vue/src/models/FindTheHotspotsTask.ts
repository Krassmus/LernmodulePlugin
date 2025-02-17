import { z } from 'zod';
import { imageElementSchema } from '@/models/common';

const rectangleHotspotSchema = z.object({
  uuid: z.string(),
  type: z.literal('rectangle'),
  x: z.number(),
  y: z.number(),
  width: z.number(),
  height: z.number(),
});
export type RectangleHotspot = z.infer<typeof rectangleHotspotSchema>;

const circleHotspotSchema = z.object({
  uuid: z.string(),
  type: z.literal('circle'),
  x: z.number(),
  y: z.number(),
  diameter: z.number(),
});
export type CircleHotspot = z.infer<typeof circleHotspotSchema>;

export const hotspotSchema = z.union([
  rectangleHotspotSchema,
  circleHotspotSchema,
]);
export type Hotspot = z.infer<typeof hotspotSchema>;
export type HotspotType = Hotspot['type'];

export const findTheHotspotsTaskSchema = z.object({
  task_type: z.literal('FindTheHotspots'),
  image: imageElementSchema,
  hotspots: z.array(hotspotSchema),
});
export type FindTheHotspotsTask = z.infer<typeof findTheHotspotsTaskSchema>;
