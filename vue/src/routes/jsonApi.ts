import axios from 'axios';

export const httpClient = axios.create({
  baseURL: window.STUDIP.URLHelper.getURL(`jsonapi.php/v1`, {}, true),
  headers: {
    'Content-Type': 'application/vnd.api+json',
  },
});

export async function createFile({
  file,
  fileData,
  folder,
}: {
  file: StudipFile;
  fileData: File | Blob;
  folder: Pick<Folder, 'id'>;
}) {
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
  let response = null;
  try {
    response = await httpClient.get(request.headers.location);
  } catch (e) {
    console.debug(e);
    response = null;
  }
  return response ? response.data.data : response;
}

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
