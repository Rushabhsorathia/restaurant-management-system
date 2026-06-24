# RMS-054: Owner Community Feature

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-054 |
| **Type** | Story |
| **Epic** | Platform & Integrations |
| **Milestone** | Milestone 6 - Platform & Integrations |
| **Priority** | Low |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-001 (Auth & Multi-Tenancy), RMS-027 (Reviews & Ratings) |

## User Story
As a restaurant owner using the RMS, I want to join a private community of fellow restaurant owners where I can ask questions, share best practices, read vendor partner content, and earn badges for active participation, so that I learn from peers and get more value out of the platform beyond the software.

## Description
Restaurant owners face similar operational challenges -- menu engineering, staff training, cost control, marketing tactics, regulatory compliance -- and benefit enormously from peer knowledge. This story adds a community layer on top of the RMS: a private, gated social space where verified restaurant owners can post, discuss, share wins, ask for help, and earn recognition. The community is not a public forum; it is exclusive to RMS customers to keep signal high and noise low.

The community module offers forums organized by topic (Operations, Marketing, Finance, Tech, Staff, Compliance, Recipes, Equipment), peer Q&A with upvoting and accepted-answer marking, an announcements channel (vendor partnerships, regulatory updates, RMS feature releases), best-practice library (curated articles by RMS staff and invited experts), and a rewards/badges system that gamifies participation.

The rewards system awards points and badges for: posting questions, answering questions, having an answer accepted, posting a best practice, getting upvotes, daily streaks, and milestones (first post, 10th answer, etc.). Badges are visual achievements (Bronze/Silver/Gold tiers per category) and contribute to a public profile "Reputation" score. Top contributors each month are featured on a leaderboard.

Vendor partnerships are surfaced as sponsored content in the announcements channel with disclosure tags. Vendor partners (from RMS-053 supplier marketplace, RMS-051 aggregators, RMS-052 accounting, etc.) get a verified Vendor badge and can post educational content. The community admin (RMS staff) can pin posts, lock threads, mark spam, and feature content.

Privacy: all community data is internal; posts are visible only to RMS verified users. No public search engine indexing. Users can opt out of community access in their profile without losing RMS functionality.

## Acceptance Criteria
- [ ] Private community gated to verified RMS restaurant owners; non-owners see "Join the community" prompt.
- [ ] Forums by topic: Operations, Marketing, Finance, Tech, Staff, Compliance, Recipes, Equipment.
- [ ] Create post (text + image + tags); rich text editor with markdown support.
- [ ] Reply/thread support with nested replies (1 level of nesting).
- [ ] Upvote / downvote on posts and replies; one vote per user per item; toggleable.
- [ ] Mark "Best Answer" / "Accepted Answer" on replies (only post author can).
- [ ] Announcements channel: pinned posts, vendor partnership content with disclosure badge.
- [ ] Best-practice library: curated long-form articles with author byline and reading time.
- [ ] Search across posts by title, body, tags; filter by forum, date, author.
- [ ] Rewards system: points for actions, badges for milestones, leaderboard.
- [ ] Profile page shows user's posts, replies, badges, reputation.
- [ ] Notifications: replies to your posts, mentions, accepted answers, new announcements.
- [ ] Admin moderation: pin, lock, mark spam, feature, delete; mod log.
- [ ] User opt-out in profile hides community from nav and disables notifications.
- [ ] All content text-searchable via Postgres full-text or Scout/Meilisearch.

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Community Home | /community | Personalized feed: latest, trending, your topics |
| Forum View | /community/f/{slug} | Posts list in a forum |
| Post Detail | /community/posts/{id} | Threaded discussion |
| New Post | /community/posts/new | Composer |
| Best Practices | /community/best-practices | Curated articles |
| Leaderboard | /community/leaderboard | Top contributors |
| User Profile (Community) | /community/u/{username} | Posts, badges, reputation |
| Announcements | /community/announcements | Pinned posts and vendor content |
| Admin Moderation | /community/admin | Mod queue and tools |

### Screen Details

**Community Home (/community)**: Top tabs: Latest, Trending (7d), Following, Announcements, Unread. Hero: "Best Practice of the Week" featured card. Sidebar: forums with unread counts, "Your Topics", leaderboard mini. Post card: avatar, username, reputation badge, time ago, title, snippet, tags, vote/reply/view counts. Compose FAB. Infinite scroll.

**Forum View (/community/f/{slug})**: Header: forum name, description, "Subscribe" toggle, post count, "New Post" button. Sort: Latest, Top (week/month/all), Most Replies. Pinned posts at top.

**Post Detail (/community/posts/{id})**: Title, tags, author info, vote/share/bookmark/report. Body: rendered markdown with images, code, tables. Replies: nested 1 level with vote/accept/reply. Composer at bottom. Side panel: related posts, author stats.

**New Post (/community/posts/new)**: Forum dropdown, title input, tag multi-select, TipTap rich text editor (image, code, link, table), preview tab. Buttons: "Save Draft", "Post", "Cancel".

**Best Practices (/community/best-practices)**: Hero carousel of editor's pick. Article card grid: cover, title, summary, author, reading time, category. Filters: Category, Author, Reading time. Sidebar: top authors, most-read.

**Leaderboard (/community/leaderboard)**: Period selector (Week/Month/All Time), tabs (Reputation/Posts/Replies/Accepted). Table: Rank | User | Points | Posts | Replies | Badges | Trend. Top 3 with podium.

**User Profile (/community/u/{username})**: Header: avatar, username, bio, joined date, reputation, badges showcase. Tabs: Posts, Replies, Best Answers, Bookmarks, Badges (grid w/ tier + earned date; locked grayed w/ requirement). Stats: posts, replies, accepted, total upvotes.

**Announcements (/community/announcements)**: Pinned posts from RMS team and verified vendors. Vendor content with "Sponsored" disclosure badge. "All Announcements" subscribe toggle.

**Admin Moderation (/community/admin)**: Mod queue: reported posts, spam flags, pending verifications. Mod actions: Pin, Lock, Feature, Delete, Warn User, Ban User. Mod log: every action with actor/target/reason. User management: ban/unban, badge grants, reputation adjustments.

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/community/forums | List forums with unread counts |
| GET | /api/v1/community/posts | List posts (filter: forum, tag, sort, search) |
| POST | /api/v1/community/posts | Create post |
| GET | /api/v1/community/posts/{id} | Get post detail with replies |
| PUT | /api/v1/community/posts/{id} | Edit post (author or mod) |
| DELETE | /api/v1/community/posts/{id} | Delete post |
| POST | /api/v1/community/posts/{id}/vote | Upvote/downvote |
| POST | /api/v1/community/posts/{id}/pin | Pin (mod) |
| POST | /api/v1/community/posts/{id}/lock | Lock (mod) |
| POST | /api/v1/community/posts/{id}/feature | Feature (mod) |
| POST | /api/v1/community/posts/{id}/bookmark | Bookmark |
| GET | /api/v1/community/posts/{id}/replies | List replies |
| POST | /api/v1/community/posts/{id}/replies | Post reply |
| POST | /api/v1/community/replies/{id}/accept | Mark accepted answer |
| GET | /api/v1/community/tags | List tags |
| GET | /api/v1/community/leaderboard | Get leaderboard |
| GET | /api/v1/community/users/{username} | Get public profile |
| GET | /api/v1/community/users/me/badges | List my badges |
| GET | /api/v1/community/notifications | List notifications |
| PATCH | /api/v1/community/notifications/{id}/read | Mark read |
| POST | /api/v1/community/follow/{userId} | Follow user |
| POST | /api/v1/community/forum/{id}/subscribe | Subscribe to forum |
| POST | /api/v1/community/reports | Report content |
| GET | /api/v1/community/admin/mod-queue | Mod queue |
| GET | /api/v1/community/admin/mod-log | Mod log |

## Database Tables

**community_forums**
- id (bigIncrements, PK)
- slug (string, unique)
- name (string)
- description (text, nullable)
- icon (string, nullable)
- sort_order (unsignedInteger)
- is_active (boolean, default true)
- is_announcement_only (boolean, default false)
- post_count (unsignedInteger, default 0)
- last_post_at (timestamp, nullable)
- timestamps

**community_posts**
- id (bigIncrements, PK)
- forum_id (foreignId, community_forums)
- author_user_id (foreignId, users)
- title (string), slug (string, unique)
- body_markdown (text), body_html (text), excerpt (string)
- tags_json (json) -- array of tag slugs
- vote_count (integer, default 0), reply_count (unsignedInteger, default 0), view_count (unsignedInteger, default 0)
- is_pinned, is_locked, is_featured, is_announcement, is_vendor_post (booleans, default false)
- vendor_partner_id (foreignId, vendor_partners, nullable)
- accepted_reply_id (foreignId, community_replies, nullable)
- last_activity_at (timestamp, nullable), created_at, updated_at
- fulltext([title, body_markdown])
- index([forum_id, is_pinned, last_activity_at])

**community_replies**
- id (bigIncrements, PK)
- post_id (foreignId, community_posts)
- parent_reply_id (foreignId, community_replies, nullable)
- author_user_id (foreignId, users)
- body_markdown (text), body_html (text)
- vote_count (integer, default 0), is_accepted (boolean, default false)
- accepted_at (timestamp, nullable), created_at, updated_at
- index([post_id, vote_count])
- index([parent_reply_id])

**community_votes**
- id (bigIncrements, PK)
- votable_type (string) -- `post` or `reply`
- votable_id (unsignedBigInteger)
- user_id (foreignId, users)
- value (tinyInteger) -- +1 or -1
- created_at
- unique([votable_type, votable_id, user_id])
- index([votable_type, votable_id])

**community_tags**
- id (bigIncrements, PK)
- slug (string, unique)
- name (string)
- description (text, nullable)
- post_count (unsignedInteger, default 0)
- timestamps

**community_user_points**
- id (bigIncrements, PK)
- user_id (foreignId, users, unique)
- reputation (integer, default 0)
- posts_count (unsignedInteger, default 0)
- replies_count (unsignedInteger, default 0)
- accepted_answers_count (unsignedInteger, default 0)
- upvotes_received (integer, default 0)
- best_practices_count (unsignedInteger, default 0)
- daily_streak (unsignedSmallInteger, default 0)
- last_active_date (date, nullable)
- updated_at

**community_badges**
- id (bigIncrements, PK)
- slug (string, unique)
- name (string)
- description (text)
- tier (enum: bronze, silver, gold)
- category (enum: posting, replying, engagement, milestone, special)
- icon (string)
- requirement_json (json) -- e.g., `{type: "posts_count", value: 10}`
- timestamps

**community_user_badges** (pivot)
- id (bigIncrements, PK)
- user_id (foreignId, users)
- badge_id (foreignId, community_badges)
- earned_at (timestamp)
- unique([user_id, badge_id])

**community_subscriptions**
- id (bigIncrements, PK)
- user_id (foreignId, users)
- subscribable_type (string) -- `forum`, `post`, `user`
- subscribable_id (unsignedBigInteger)
- created_at
- unique([user_id, subscribable_type, subscribable_id])

**community_reports**
- id (bigIncrements, PK)
- reporter_user_id (foreignId, users)
- reportable_type (string) -- `post`, `reply`
- reportable_id (unsignedBigInteger)
- reason (enum: spam, harassment, off_topic, misinformation, other)
- details (text, nullable)
- status (enum: pending, resolved, dismissed)
- resolved_by (foreignId, users, nullable)
- resolved_at (timestamp, nullable)
- created_at

## Technical Notes

**Laravel Backend:**
- `CommunityService` orchestrates post create, vote, accept, reply flows.
- `ReputationService::awardForAction()` increments points per action (post_created=2, reply=1, accepted=15, upvote_received=5).
- `BadgeService::evaluate()` runs after reputation change, checks requirements, awards badges.
- Daily streak: `last_active_date` checked on each action; gap resets to 1.
- `VoteObserver` updates `vote_count`; only post author can accept answer; one accepted per post.
- Markdown: `league/commonmark` with extensions for @mentions, #tags, !images.
- Search: Scout + Meilisearch for fast post search; in-app + email + push notifications.
- Mod log records every action (actor, target, reason); rate-limit new users (5 posts/day); moderation queue for first 3 posts.
- `users.community_opted_out` boolean disables nav, notifications, and creation.

**React Frontend:**
- Community home with React Query + infinite scroll; TipTap composer for rich text.
- Threaded reply UI; optimistic vote updates; leaderboard with podium for top 3.
- Badge grid with locked/unlocked states; notification bell in top bar; mod tools inline.

## Subtasks
1. [ ] Create community tables and models
2. [ ] Build `CommunityService` with post/vote/accept/reply flows
3. [ ] Build `ReputationService` and `BadgeService`
4. [ ] Implement markdown rendering with @mentions, #tags
5. [ ] Set up Scout + Meilisearch for post search
6. [ ] Build forum subscription, post bookmark, user follow
7. [ ] Build notification system for reply, mention, accepted, announcement
8. [ ] Implement rate limiting and new-user moderation queue
9. [ ] Build mod tools (pin, lock, feature, delete, ban) with mod log
10. [ ] Seed initial forums, badges, and sample posts
11. [ ] Build Community Home with tabs and infinite scroll
12. [ ] Build Forum View, Post Detail, New Post composer (TipTap)
13. [ ] Build Best Practices library and Leaderboard
14. [ ] Build User Profile (community) with badges showcase
15. [ ] Build Admin Moderation UI
16. [ ] Add opt-out in user preferences
17. [ ] Write tests for reputation, badges, voting, mod actions, search, notifications

## Testing Criteria
- [ ] User creates a post; appears in forum with correct vote/reply counts
- [ ] Upvote increments `vote_count`; revote toggles; double-vote rejected
- [ ] Accepted answer sets `is_accepted=true`, awards 15 points to reply author
- [ ] New user crosses 10-post milestone and earns "Engaged" bronze badge
- [ ] Daily streak increments on consecutive days; resets after 1 day gap
- [ ] Search returns post with title match in <200ms
- [ ] Mod can pin post; pinned post appears at top regardless of sort
- [ ] Reported post enters mod queue; mod can dismiss or take action
- [ ] Opted-out user does not appear in leaderboard and cannot create posts
- [ ] New user limited to 5 posts/day; 6th returns 429
- [ ] Email notification sent on mention within 60s
- [ ] Reputation leaderboard updates within 30s of action
