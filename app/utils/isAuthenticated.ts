export const isAuthenticated = (): boolean => {
  const userStore = useUserStore();
  return !!userStore.token && !!userStore.user;
};
