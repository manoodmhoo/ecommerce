# Laravel E-Commerce API

### API documentation:

> POST /api/auth/login
> POST /api/auth/logout
> GET /api/carts
> GET /api/carts/{cart}
> POST /api/carts/{cart}
> DELETE /api/carts/{cart}
> POST /api/carts/{cart}/checkout
> DELETE /api/carts/{cart}/items/{item}
> GET /api/category
> POST /api/category
> GET /api/category/{category}
> PUT /api/category/{category}
> DELETE /api/category/{category}
> GET /api/product
> POST /api/product
> GET /api/product/{product}
> PUT /api/product/{product}
> DELETE /api/product/{product}
> GET /api/products
> GET /api/social/login/{provider}
> GET /api/social/login/{provider}/callback

### Installation:

$ composer install
$ php artisan key:generate
$ php artisan migrate
$ php artisan db:seed

### License

MIT
