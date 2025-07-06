
# **bd**

> An anonymous, minimal **imageboard with absolutely no images** written in `php`

## Overview

**bd** is a minimalist, anonymous imageboard with absolutely **no image support** inspired by early 2000s forums and boards

Check out the [bd.juanvel400.xyz](https://bd.juanvel400.xyz) public instance
## Features

- Anonymous posting and commenting
- Simple, table-based boards
- Deletion keys for post/comment management
- Deletion keys are hashed with `bcrypt` for security
- Extensible and customizable
## Quickstart
### Dependencies
- `git`: for everything
- `php` (with `pdo`): to run the development server
- `vim` (or any text editor): if you want to customize **bd**
- `bash`: to generate `VERSION` and `bd/version.php`

### Setup
 1. **Clone** this repository
```sh
git clone https://github.com/juanvel4000/bd
```
 2. **Generate** a `VERSION` and `bd/version.php`, optional but recommended
 ```sh
 ./write-version.sh
 ```
 3. **Update `bd/core.php`** with your custom configurations
```sh
vim bd/core.php
```

 4. **Navigate** to the `bd/` directory
```sh
cd bd
```

 5. **Run** with the `PHP` development server 

```sh
php -S http://localhost:8000 index.php
```
## Acknowledgements

 - [bramus/router](https://github.com/bramus/router): Wonderful **routing** system (at `/bd/include/Router.php`)
## License

**bd** is licensed with the [MIT License](https://choosealicense.com/licenses/mit/)

