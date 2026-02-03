# GetSongBPM API Documentation

Doc trouvée sur [https://getsongbpm.com/api](https://getsongbpm.com/api)

## API For Developers

### Get your API Key

To get your API key, you need to provide the following information:

*   **Website URL or App ID/Package Name**:
    *   Prepend Android Package name with `android-app://`
    *   Prepend iOS App ID with `ios-app://`
    *   (ex. `android-app://com.package_name`, `ios-app://app_id`).
*   **Backlink URL**: A backlink is mandatory; please add it before requesting access.
*   **Email**: A valid email is required to activate your API Key.

Our Web API enables external applications to access our database of BPM and song keys. It allows developers to build programs using getsongbpm's data on mobile devices or other web services. All you need is a valid API key.

Using our API is free, but a link back to GetSongBPM.com is **REQUIRED** (website or store listing), or we will suspend your account without notice.

### Introduction

Any application can access our API endpoints but must first be registered with a valid email address. To register your application, fill in the form on the left (or top on mobiles) of this page.

Whether your project is under development, for private use, educational or commercial purposes, adding a backlink to getsongbpm.com is mandatory. We have no way of getting around this restriction. We're sorry for the inconvenience, but there's been way too much abuse. The API is free; the only thing we ask in return is a link to support us. Thank you for your understanding.

Feel free to contact us if you have any questions.

### Get Started

*   **Web API Base URL**: `https://api.getsong.co/`
*   **Method**: `GET`
*   **Authorization**: A valid API Key must be sent with all client requests, either via URL_PARAM (`api_key`), or a `X-API-KEY` header parameter. Unauthenticated requests are not allowed, and a limit of 3000 requests per hour is applied. (If you exceed this number, your key will be blocked for one hour.)

### Endpoint Reference

Using our web API is very easy: endpoints return metadata in JSON format about artists and songs.

#### `/search/`

*   **Params**:
    *   `type` (required): `"song"`, `"artist"` or `"both"`
    *   `lookup` (required): Song title (urlencoded) or Artist name (urlencoded), depending on selected type. For `"both"` type, prepend searched terms with `"song:"` and `"artist:"`, like that: `lookup=song:enter+sandman artist:metallica`
    *   `limit` (optional): The number of results to fetch. (Default: 10 for `"both"`, 20 for `"artist"`, 30 for `"song"`)
*   **Returns**: Array of song(s) or artist(s) matching your query.

#### `/artist/`

*   **Params**:
    *   `id` (required): artist ID
*   **Returns**: Artist infos.

#### `/song/`

*   **Params**:
    *   `id` (required): song ID
*   **Returns**: Details about a song.

#### `/tempo/`

*   **Params**:
    *   `bpm` (required): target BPM (allowed range: 40-220 BPM)
    *   `limit` (optional): The number of results to fetch. (By default, results are limited to the 250 most viewed songs in the last 30 days).
*   **Returns**: Songs in the defined BPM or BPM range.

#### `/key/`

*   **Params**:
    *   `key_of` (required): Key to find (0: C, 1: C♯, etc.)
    *   `mode` (required): Major (1) or Minor (0)
    *   `type` (optional): notation, either `"flat"` or `"sharp"` (default).
    *   `limit` (optional): The number of results to fetch. (By default, results are limited to the 250 most viewed songs in the last 30 days.)
*   **Returns**: Songs in the specified Key.

### Responses

On success, the HTTP status code in the response header is `200 OK`, and the response body contains an array of values in JSON format.

On error, the header status code is an error code, and the response body contains an error.

### Search Object

*   **`"artist"` type Query**: Returns a list of artists matching your query. See "Artist" Object.
*   **`"both"` and `"song"` type queries**: Returns a list of songs matching your query, optionally refined with an artist name.

| Key          | Value Type | Value Description                                                                 |
| :----------- | :--------- | :-------------------------------------------------------------------------------- |
| `id`         | `String`   | The GetSong ID for the song.                                                      |
| `title`      | `String`   | The title of the song.                                                            |
| `uri`        | `String`   | The GetSong URI for the song.                                                     |
| `tempo`      | `Integer`  | Beat per minute of the song.                                                      |
| `time_sig`   | `Integer`  | Time signature (beta).                                                            |
| `key_of`     | `String`   | Original published key of the song.                                               |
| `open_key`   | `String`   | Key name in open key notation (Traktor).                                          |
| `danceability` | `Integer`  | From 0 to 100.                                                                    |
| `acousticness` | `Integer`  | From 0 to 100.                                                                    |
| `artist`     | `Array`    | Same as the "Artist" Object, except without similar artists.                      |
| `album`      | `Array`    | Album name, The GetSong URI for the album, Release Year (contains `title`, `uri`, `year`). |

### Artist Object

| Key       | Value Type | Value Description                                                 |
| :-------- | :--------- | :---------------------------------------------------------------- |
| `id`      | `String`   | The artist's GetSong ID.                                          |
| `name`    | `String`   | The name of the artist.                                           |
| `uri`     | `String`   | The GetSong URI for the artist.                                   |
| `genres`  | `Array`    | Artist main genres.                                               |
| `from`    | `String`   | Country or region/city of origin.                                 |
| `mbid`    | `String`   | MusicBrainz ID.                                                   |
| `similar` | `Array`    | List of 5 similar artists (same keys/values as the artist object, except without similar artists). |

### Song Object

| Key          | Value Type | Value Description                                                                 |
| :----------- | :--------- | :-------------------------------------------------------------------------------- |
| `id`         | `String`   | The GetSong ID for the song.                                                      |
| `title`      | `String`   | The title of the song.                                                            |
| `uri`        | `String`   | The GetSong URI for the song.                                                     |
| `tempo`      | `Integer`  | Beat per minute of the song.                                                      |
| `time_sig`   | `Integer`  | Time signature (beta).                                                            |
| `key_of`     | `String`   | Original published key of the song.                                               |
| `open_key`   | `String`   | Key name in open key notation (Traktor).                                          |
| `danceability` | `Integer`  | From 0 to 100.                                                                    |
| `acousticness` | `Integer`  | From 0 to 100.                                                                    |
| `artist`     | `Array`    | Same as the "Artist" Object, except without similar artists.                      |
| `album`      | `Array`    | Album name, The GetSong URI for the album, Release Year (contains `title`, `uri`, `year`). |

### Tempo Object

| Key        | Value Type | Value Description                                                                 |
| :--------- | :--------- | :-------------------------------------------------------------------------------- |
| `song_id`  | `String`   | The GetSong ID for the song.                                                      |
| `song_title` | `String`   | The title of the song.                                                            |
| `song_uri` | `String`   | The GetSong URI for the song.                                                     |
| `tempo`    | `Integer`  | Beat per minute of the song.                                                      |
| `artist`   | `Array`    | See "Artist" object.                                                              |
| `album`    | `Array`    | Album name, The GetSong URI for the album, Release Year (contains `title`, `uri`, `year`). |

### Key Object

| Key        | Value Type | Value Description                                                                 |
| :--------- | :--------- | :-------------------------------------------------------------------------------- |
| `song_id`  | `String`   | The GetSong ID for the song.                                                      |
| `song_title` | `String`   | The title of the song.                                                            |
| `song_uri` | `String`   | The GetSong URI for the song.                                                     |
| `music_key` | `Array`    | Contains `raw` (input query), `key_of` (English name of the Key), `mode` (`"major"` or `"minor"`). |
| `artist`   | `Array`    | See "Artist" object.                                                              |
| `album`    | `Array`    | Album name, The GetSong URI for the album, Release Year (contains `title`, `uri`, `year`). |

### Examples

#### Search for an artist

Search for "The Offspring".

```bash
curl -X GET "https://api.getsongbpm.com/search/?api_key=YOUR_API_KEY_HERE&type=artist&lookup=the+offspring"
```

Returns:

```json
{
    "search": [
        {
            "id": "N8K",
            "name": "The Offspring",
            "uri": "https://getsongbpm.com/artist/the-offspring/N8K",
            "genres": ["punk", "rock"],
            "from": "US",
            "mbid": "23a03e33-a603-404e-bcbf-2c00159d7067",
            "similar": [
                {
                    "id": "v9M",
                    "name": "Green Day",
                    "uri": "https://getsongbpm.com/artist/green-day/v9M",
                    "genres": ["pop", "punk", "rock"],
                    "from": "US",
                    "mbid": "084308bd-1654-436f-ba03-df6697104e19"
                }
            ]
        }
    ]
}
```

#### Get the BPM or Key of a song

Get the BPM or Key of "Master of Puppets" by "Metallica".

```bash
curl -X GET "https://api.getsongbpm.com/song/?api_key=YOUR_API_KEY_HERE&id=o2r0L"
```

Returns:

```json
{
    "song": {
        "id": "o2r0L",
        "title": "Master of Puppets",
        "uri": "https://getsongbpm.com/song/master-of-puppets/o2r0L",
        "tempo": "220",
        "time_sig": "4/4",
        "key_of": "Em",
        "open_key": "2m",
        "danceability": 55,
        "acousticness": 0,
        "artist": {
            "id": "nZR",
            "name": "Metallica",
            "uri": "https://getsongbpm.com/artist/metallica/nZR",
            "genres": ["heavy metal", "rock"],
            "from": "US",
            "mbid": "65f4f0c5-ef9e-490c-aee3-909e7ae6b2ab"
        }
    }
}
```

### Changelog

| Version | Date       | Description                                                                                                                                     |
| :------ | :--------- | :---------------------------------------------------------------------------------------------------------------------------------------------- |
| 1.3     | 11/12/2024 | Special update to help developers stuck with their app in development after Spotify's announcement to limit results without notice. Added danceability and acousticness values for songs (via acousticbrainz). Added similar artists at the `/search/` (artist type) and `/artist/` endpoints. |
| 1.2     | 25/09/2024 | API is now available under this domain: `https://api.getsong.co`. API calls using the old base URL are automatically redirected. Removed album cover and artist photo links to third-party image providers. |
| 1.1.2   | 23/03/2022 | Added `limit` parameter for `/search/`, `/tempo/` and `/key/` endpoints.                                                                       |
| 1.1.1   | 12/11/2019 | Added `/key/` endpoint.                                                                                                                         |
| 1.1     | 01/08/2019 | Added `/tempo/` endpoint. Added artist nationality and genres where relevant. Changed response format for artists: data returned follows keys/values model of the artist object. Removed artist short biography bio at `/artist/` endpoint. Fixed empty artist photo at `/song/` endpoint. |
| 1.0     | 02/02/2017 | Initial release.                                                                                                                                |

Go To Top

### About Us

We are the largest database of beats per minutes in the world. Get the Tempo of more than 6 Million songs.

*   About
*   FAQ
*   API
*   Contact
*   Privacy Policy

### Top Songs

*   Hotel California (Eagles)
*   6 Sonaten, Wq 63 no. 1 in C major: III. Tempo di mineutto con tenerezza (Christopher Hogwood)
*   Zombie (The Cranberries)
*   Nothing Else Matters (Metallica)
*   Symphony no. 4 in B-flat major, op. 20: I. Andantino - Allegro vivace e grazioso (Christopher Hogwood)
*   Master of Puppets (Metallica)

### Top Artists

*   Christopher Hogwood
*   Kendrick Lamar
*   Eminem
*   Kanye West
*   Tyler, the Creator
*   Ariana Grande

### Top Albums

*   Playboi Carti (Playboi Carti)
*   After Hours (The Weeknd)
*   Taylor Swift (Taylor Swift)
*   FUTURE (Future)
*   ASTROWORLD (Travis Scott)
*   DAMN. (Kendrick Lamar)

Made with ❤️ in Berlin.
musicbrainz logo lastfm logo spotify logo