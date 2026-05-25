# Roadmap

## User Profile Page

### Features to implement

1. **Profile image upload**
   - During registration: add an avatar upload field to the registration form
   - On the profile page: allow the user to change their avatar after registration
   - Store the image as user meta (or as a WordPress attachment linked to the user)
   - Show a default placeholder when no avatar is set

2. **User profile page**
   - A dedicated page/template that displays user info: username, avatar, registration date, league stats (wins, losses, tournaments played, etc.)
   - Route: `/giocatore/{username}` or `/player/{username}` (custom rewrite rule or CPT)
   - Data pulled from user meta + aggregated from Tappa/decklist data

3. **Leaderboard → profile linking**
   - Every player name in the leaderboard (and anywhere else users are listed) becomes a clickable link pointing to their profile page

### Implementation notes

- Avatar storage: WordPress `wp_usermeta` (key `paupero_avatar_id`) storing an attachment ID, served via `wp_get_attachment_image_url()`
- Profile page template: `resources/views/template-player-profile.blade.php` (Blade), with a dedicated View Composer in `app/View/Composers/PlayerProfile.php`
- Upload handling: custom REST endpoint or standard `wp_handle_upload()` in an AJAX handler, protected by nonce
- Registration hook: `user_register` or a filter on the registration form to process the optional avatar upload
