import axios from 'axios';
import { z } from 'zod';

export const httpClient = axios.create({
  baseURL: window.STUDIP.URLHelper.getURL(`jsonapi.php/v1`, {}, true),
  headers: {
    'Content-Type': 'application/vnd.api+json',
  },
});

/**
 * Send a request to the JSON API to create a file. Adapted from the file
 * "courseware.module.js" in the Stud.IP core.
 */
export async function createFile({
  file,
  fileData,
  folder,
}: {
  file: StudipFile;
  fileData: File | Blob;
  folder: Pick<Folder, 'id'>;
}): Promise<CreateFileResponse> {
  const termId = file?.relationships?.['terms-of-use']?.data?.id ?? null;
  const formData = new FormData();
  formData.append('file', fileData, file.attributes.name);
  if (termId) {
    formData.append('term-id', termId);
  }
  const url = `folders/${folder.id}/file-refs`;
  const request = await httpClient.post(url, formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });
  const response = await httpClient.get(request.headers.location);
  const data = response.data.data;
  return createFileResponseSchema.parse(data);
}

const createFileResponseSchema = z.object({
  type: z.literal('file-refs'),
  id: z.string(),
  attributes: z.object({
    name: z.string(),
    mkdate: z.string(),
    chdate: z.string(),
    description: z.string(),
    downloads: z.number(),
    filesize: z.number(),
    'is-downloadable': z.boolean(),
    'is-editable': z.boolean(),
    'is-readable': z.boolean(),
    'is-writable': z.boolean(),
    'mime-type': z.string(),
  }),
  links: z.object({
    self: z.string(),
  }),
  meta: z.object({
    'download-url': z.string(),
  }),
  // 'unknown' for the moment because I did not care to write out types for this
  // field and do not have a use for it in the code I have written thus far.
  relationships: z.unknown(),
});
export type CreateFileResponse = z.infer<typeof createFileResponseSchema>;

export interface Folder {
  id: string;
  attributes: {
    'folder-type': string;
    'data-content': {
      download_allowed: number;
    };
    name: string;
  };
  relationships?: {
    parent?: {
      data: {
        id: string;
      };
    };
  };
}

export interface StudipFile {
  relationships?: {
    'terms-of-use'?: {
      data?: {
        id?: string;
      };
    };
  };
  attributes: {
    name: string;
  };
}
