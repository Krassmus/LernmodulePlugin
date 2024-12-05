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
  file: CreateFileSpec;
  fileData: File | Blob;
  folder: Pick<FolderRef, 'id'>;
}): Promise<FileRef> {
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
  return fileRefsSchema.parse(data);
}

/**
 * Send a request to the JSON API to get all folders for a given userId.
 */
export async function getFolders({
  userId,
}: {
  userId: string;
}): Promise<FolderRef[]> {
  const url = `users/${userId}/folders`;

  // Make the GET request
  const response = await httpClient.get(url, {
    headers: {
      'Content-Type': 'application/vnd.api+json',
    },
  });

  // Return the parsed response data
  return response.data.data as FolderRef[];
}

/**
 * Send a request to the JSON API to create a folder.
 */
export async function createFolder({
  userId,
  name,
  parentFolderId,
}: {
  userId: string;
  name: string;
  parentFolderId: string;
}): Promise<FolderRef> {
  // Construct the endpoint for creating a folder for a user
  const url = `users/${userId}/folders`;

  // Prepare the request payload
  const payload = {
    data: {
      type: 'folders',
      attributes: {
        name,
      },
      relationships: {
        parent: {
          data: {
            type: 'folders',
            id: parentFolderId,
          },
        },
      },
    },
  };

  // Make the POST request
  const response = await httpClient.post(url, payload, {
    headers: {
      'Content-Type': 'application/vnd.api+json',
    },
  });

  // Return the parsed response data
  return response.data.data as FolderRef;
}

/**
 * Datatype 'file-refs' provided by the Stud.IP JSON API.
 */
export const fileRefsSchema = z.object({
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
export type FileRef = z.infer<typeof fileRefsSchema>;

export interface FolderRef {
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

/**
 * A subset of the information contained in FileRef, containing the information
 * necessary to send a request to create a new file on the server.
 */
export interface CreateFileSpec {
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
