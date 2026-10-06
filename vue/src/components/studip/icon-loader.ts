type CacheOption = 'off' | 'session' | 'local';

type CachedIcon = {
  isSvg: boolean;
  content: string;
};

class IconLoader {
  readonly #cacheKey: string = 'studip/icons';

  #baseUrl: string;
  #useCache: CacheOption = 'off';

  #cache: Map<string, string>;
  #promises: Map<string, Promise<CachedIcon>>;

  constructor(baseUrl: string, useCache: CacheOption = 'off') {
    this.#baseUrl = baseUrl;
    this.#useCache = useCache;

    this.#cache = new Map<string, string>(this.#initialState());
    this.#promises = new Map<string, Promise<CachedIcon>>();
  }

  async load(shape: string): Promise<CachedIcon> {
    if (this.#cache.has(shape)) {
      return JSON.parse(this.#cache.get(shape)!);
    }

    if (this.#promises.has(shape)) {
      return this.#promises.get(shape)!;
    }

    const containsUrl = (shape: string): boolean =>
      /\bhttps?:\/\/[^\s]+/i.test(shape);

    const url = containsUrl(shape)
      ? shape
      : `${this.#baseUrl}images/icons/blue/${shape}.svg`;

    const promise = (async () => {
      try {
        const response = await fetch(url);
        if (!response.ok) {
          throw new Error(
            `IconLoader: HTTP ${response.status} ${response.statusText}`
          );
        }

        const icon: CachedIcon = {
          isSvg:
            response.headers.get('Content-Type')?.includes('image/svg+xml') ??
            false,
          content: '',
        };

        if (icon.isSvg) {
          let svg = await response.text();
          svg = svg.replace(/fill="(?!none)[^"]+"/g, 'fill="currentColor"');
          svg = svg.replace(/(width|height)="[^"]+"/g, '');
          icon.content = svg;
        } else {
          const blob = await response.blob();
          icon.content = await new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result as string);
            reader.readAsDataURL(blob);
          });
        }

        this.store(shape, icon);

        return icon;
      } catch (error) {
        console.error(`IconLoader: Fehler beim Laden von ${shape}`, error);
        return {
          isSvg: true,
          content: '',
        } as CachedIcon;
      } finally {
        this.#promises.delete(shape);
      }
    })();

    this.#promises.set(shape, promise);

    return promise;
  }

  store(shape: string, icon: CachedIcon): void {
    this.#cache.set(shape, JSON.stringify(icon));

    this.#getStorage()?.setItem(
      this.#cacheKey,
      JSON.stringify([...this.#cache])
    );
  }

  #getStorage(): Storage | null {
    if (this.#useCache === 'off') {
      return null;
    }
    return this.#useCache === 'session' ? sessionStorage : localStorage;
  }

  #initialState(): [string, string][] {
    const cached = this.#getStorage()?.getItem(this.#cacheKey);
    if (!cached) {
      return [];
    }

    try {
      return JSON.parse(cached);
    } catch {
      return [];
    }
  }
}

const defaultLoader = new IconLoader(window.STUDIP.ASSETS_URL, 'session');

export default defaultLoader;
export { IconLoader, CachedIcon };
