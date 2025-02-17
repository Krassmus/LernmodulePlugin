import { z } from 'zod';

export const imageElementSchema = z.object({
  uuid: z.string(),
  type: z.literal('image'),
  file_id: z.string(),
  altText: z.string(),
});
export type ImageElement = z.infer<typeof imageElementSchema>;
