// Format video timestamp for timeline axis. e.g. 1m30s becomes 00;01;30;00
export function formatVideoTimestamp(
  timestampSeconds: number,
  showCentiseconds: boolean = true
): string {
  let hours = 0,
    minutes = 0,
    seconds = 0,
    centiseconds = 0;
  while (timestampSeconds >= 3600) {
    hours++;
    timestampSeconds -= 3600;
  }
  while (timestampSeconds >= 60) {
    minutes++;
    timestampSeconds -= 60;
  }
  while (timestampSeconds >= 1) {
    seconds++;
    timestampSeconds -= 1;
  }
  centiseconds = Math.floor(timestampSeconds * 100);
  function twoDigits(n: number): string {
    return n.toLocaleString('de-DE', {
      minimumIntegerDigits: 2,
      maximumFractionDigits: 0,
    });
  }
  const short = `${twoDigits(hours)};${twoDigits(minutes)};${twoDigits(
    seconds
  )}`;
  if (showCentiseconds) {
    const long = short.concat(`;${twoDigits(centiseconds)}`);
    return long;
  } else {
    return short;
  }
}
