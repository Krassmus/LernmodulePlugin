import { z } from 'zod';

const colorBackground = z.object({
  type: z.literal('color'),
  color: z.string(),
});
const imageBackground = z.object({
  type: z.literal('image'),
  file_id: z.string(),
});

const slideElementCommon = z.object({
  x: z.number(), // Position, as a fraction of canvas width, between 0 and 1
  y: z.number(), // Position, as a fraction of canvas height, between 0 and 1
  width: z.number(), // Width, as a fraction of canvas width, between 0 and 1
  height: z.number(), // Height, as a fraction of canvas height, between 0 and 1
});
const imageElement = slideElementCommon.extend({
  type: z.literal('image'),
  file_id: z.string(),
  altText: z.string(),
});
const textElement = slideElementCommon.extend({
  type: z.literal('text'),
  contents: z.string(),
});
const linkElement = slideElementCommon.extend({
  type: z.literal('link'),
  url: z.string(),
});
const slideElement = z.discriminatedUnion('type', [
  imageElement,
  textElement,
  linkElement,
]);

const slide = z.object({
  title: z.string(),
  background: z.discriminatedUnion('type', [colorBackground, imageBackground]),
  elements: z.array(slideElement),
});

export const coursePresentationTaskSchema = z.object({
  task_type: z.literal('CoursePresentation'),
  slides: z.array(slide),
  options: z.object({}),
});
export type CoursePresentationTask = z.infer<
  typeof coursePresentationTaskSchema
>;

export function newCoursePresentationTask(): CoursePresentationTask {
  return {
    slides: [],
    task_type: 'CoursePresentation',
    options: {},
  };
}
