import { z } from 'zod';

const noImageSchema = z.object({
  type: z.literal('none'),
});

export const imageFileSchema = z.object({
  uuid: z.string(),
  type: z.literal('image'),
  file_id: z.string(),
  altText: z.string(),
});
export type ImageFileElement = z.infer<typeof imageFileSchema>;

export const imageSchema = z.union([noImageSchema, imageFileSchema]);
export type ImageElement = z.infer<typeof imageSchema>;
