import { z } from 'zod';

export const imageFileSchema = z.object({
  uuid: z.string(),
  type: z.literal('image'),
  file_id: z.string(),
  altText: z.string(),
});
export type ImageFileElement = z.infer<typeof imageFileSchema>;
