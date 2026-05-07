export function formatCurrency(value: number) {
  return `NGN ${Math.round(value).toLocaleString('en-NG')}`;
}
