# Font licences

## SolaimanLipi.woff2
- Source: OmicronLab Bangla fonts page, file `SolaimanLipi_20-04-07.ttf` (font version 1.6.1, designer Solaiman Karim, ekushey.org).
- Licence, as embedded in the font's own name table (IDs 0 and 13), quoted verbatim:
  "This Font Software is licensed under the SIL Open Font License, Version 1.0. No modification of the license is permitted, only verbatim copy is allowed. This license and a FAQ is also available at: http://scripts.sil.org/OFL"
- Changes: none to the glyphs, metrics or names. The TTF was only re-wrapped as WOFF2 for the web. It was **not** subset, because the font's embedding flags (fsType 0x0100) ask for no subsetting.
- Weight: Regular 400 only. No Bold is shipped. The theme never synthesises bold (`font-synthesis: none`).
- A licensed Bold can be added later as `assets/fonts/SolaimanLipi-Bold.woff2` (in a child theme or this theme) and switched on in Customizer → Narayanganj News.

## Inter-Regular-latin.woff2
- Source: Inter 4.1 release (github.com/rsms/inter), `web/Inter-Regular.woff2`.
- Licence: SIL Open Font License 1.1, full text in `Inter-OFL.txt`. Inter declares no Reserved Font Name.
- Changes: subset to Basic Latin, Latin-1 Supplement and common punctuation (about 16 KB). The theme only uses it for Latin letters and digits, through `unicode-range`, so Bengali pages without Latin text never download it.
