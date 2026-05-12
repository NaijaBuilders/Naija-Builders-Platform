export function formatCurrency(value: number) {
  return `NGN ${Math.round(value).toLocaleString('en-NG')}`;
}

export function capitalizeWords(value: string) {
  return value
    .trim()
    .toLowerCase()
    .replace(/\b[\p{L}\p{N}'-]/gu, (character) => character.toUpperCase());
}
