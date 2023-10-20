/**
 * A regex to extract the id from a youtube video url.
 *  Adopted from StackOverflow https://stackoverflow.com/a/27728417/7359454
 *  Accessed on 2023.10.20
 *  @license CC BY-SA 4.0
 *  @author J W
 */
export const regex =
  /^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/|shorts\/)|(?:(?:watch)?\?v(?:i)?=|&v(?:i)?=))([^#&?]*).*/;

// For testing.
const urls = [
  'https://youtube.com/shorts/dQw4w9WgXcQ?feature=share',
  '//www.youtube-nocookie.com/embed/up_lNV-yoK4?rel=0',
  'http://www.youtube.com/user/Scobleizer#p/u/1/1p3vcRhsYGo',
  'http://www.youtube.com/watch?v=cKZDdG9FTKY&feature=channel',
  'http://www.youtube.com/watch?v=yZ-K7nCVnBI&playnext_from=TL&videos=osPknwzXEas&feature=sub',
  'http://www.youtube.com/ytscreeningroom?v=NRHVzbJVx8I',
  'http://www.youtube.com/user/SilkRoadTheatre#p/a/u/2/6dwqZw0j_jY',
  'http://youtu.be/6dwqZw0j_jY',
  'http://www.youtube.com/watch?v=6dwqZw0j_jY&feature=youtu.be',
  'http://youtu.be/afa-5HQHiAs',
  'http://www.youtube.com/user/Scobleizer#p/u/1/1p3vcRhsYGo?rel=0',
  'http://www.youtube.com/watch?v=cKZDdG9FTKY&feature=channel',
  'http://www.youtube.com/watch?v=yZ-K7nCVnBI&playnext_from=TL&videos=osPknwzXEas&feature=sub',
  'http://www.youtube.com/ytscreeningroom?v=NRHVzbJVx8I',
  'http://www.youtube.com/embed/nas1rJpm7wY?rel=0',
  'http://www.youtube.com/watch?v=peFZbP64dsU',
  'http://youtube.com/v/dQw4w9WgXcQ?feature=youtube_gdata_player',
  'http://youtube.com/vi/dQw4w9WgXcQ?feature=youtube_gdata_player',
  'http://youtube.com/?v=dQw4w9WgXcQ&feature=youtube_gdata_player',
  'http://www.youtube.com/watch?v=dQw4w9WgXcQ&feature=youtube_gdata_player',
  'http://youtube.com/?vi=dQw4w9WgXcQ&feature=youtube_gdata_player',
  'http://youtube.com/watch?v=dQw4w9WgXcQ&feature=youtube_gdata_player',
  'http://youtube.com/watch?vi=dQw4w9WgXcQ&feature=youtube_gdata_player',
  'http://youtu.be/dQw4w9WgXcQ?feature=youtube_gdata_player',
];

// let i, r;
// for (i = 0; i < urls.length; ++i) {
// r = urls[i].match(regex);
// console.log(r![1]);
// }
