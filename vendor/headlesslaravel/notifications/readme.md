# Headless Notifications

<p>
    <a href="https://github.com/HeadlessLaravel/notifications/actions">
        <img src="https://github.com/HeadlessLaravel/notifications/workflows/tests/badge.svg" alt="Build Status">
    </a>
    <a href="https://packagist.org/packages/HeadlessLaravel/notifications">
        <img src="https://img.shields.io/packagist/v/HeadlessLaravel/notifications" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/HeadlessLaravel/notifications">
        <img src="https://img.shields.io/packagist/dt/HeadlessLaravel/notifications" alt="Total Downloads">
    </a>
    <a href="https://twitter.com/im_brian_d">
        <img src="https://img.shields.io/twitter/follow/im_brian_d?color=%231da1f1&label=Twitter&logo=%231da1f1&logoColor=%231da1f1&style=flat-square" alt="twitter">
    </a>
</p>


Notification Endpoints for Laravel Applications

---

### Install
Add the composer package:
```
composer require headlesslaravel/notifications
```

Call the Laravel command to add a migration.
```
php artisan notifications:table
```
For the full Laravel docs for notifications: [read more](https://laravel.com/docs/notifications)


### Available Endpoints

| Method | Endpoint | Action |
| ------ | -------- | ------ |
| get | /notifications | all |
| get | /notifications/unread | unread |
| get | /notifications/read | read |
| get | /notifications/count | count |
| post | /notifications/clear | clear |
| post | /notifications/{notification}/mark-as-read | markAsRead |
| delete | /notifications/{notification} | destroy |
