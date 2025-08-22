const validationErrors = (errors: Record<string, string[]> = {}) => {
  return Object.values(errors).flat().join(", ");
};

export default validationErrors;
