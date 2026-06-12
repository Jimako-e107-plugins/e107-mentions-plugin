# Mentions

A user @mention plugin for e107 CMS.

Based on the original plugin by [Arun S. Sekher](https://github.com/arunshekher/mentions), updated for PHP 7.4 – 8.4 and current e107 v2.

# Description

This plugin converts user mentions (usernames prepended with an '@' sign) in comments, chatbox posts and forum posts into matching user-profile links. It also renders a username auto-completion popup as you type '@' in supported text areas, and can notify mentioned users by e-mail.

# Requirements

* PHP 7.4 – 8.4
* e107 v2

# Installation

Upload the plugin directory and install it via the e107 admin plugin manager. Configure under *Admin → Mentions*.

# Known Issues (inherited from upstream)

1. Non-latin characters in usernames are not supported by the mention parser.
2. A forum title containing a word prepended with an '@' sign can cause rendering issues in the rest of the thread.
