declare global {
  function env(key: string, defaultValue?: any): any;
  function env(
    key: string,
    defaultValue?: string,
    options?: { required: boolean }
  ): any;
}

export {};
