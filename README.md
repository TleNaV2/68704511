# 68704511

## Project setup
```
npm install
```

### Database setup

Start Apache and MySQL in XAMPP, create a database named `db_shop`, and import
`php.api/db_shop.sql` using phpMyAdmin. The SQL file contains the schema and
sample product records; register a customer account to sign in. The assets API
creates the `assets` table automatically when it is first requested.

### Compiles and hot-reloads for development
```
npm run serve
```

### Compiles and minifies for production
```
npm run build
```

### Customize configuration
See [Configuration Reference](https://cli.vuejs.org/config/).
