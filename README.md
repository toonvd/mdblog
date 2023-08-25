# mdblog

A small framework that generates html files from markdown files.

## Required params:
- `--source[=SOURCE]` The source folder containing markdown files
- `--target[=TARGET]` The target folder where we need to write html to (only repo root is supported atm)                
- `--base_url[=BASE_URL]` Used for link generation          
- `--image_path[=IMAGE_PATH]` Used for link generation and to store generated images     
- `--template_path[=TEMPLATE_PATH]` Path to skeletons - [example](https://github.com/toonvd/toonvd.github.io/tree/main/.templates)
- `--should_encode[=SHOULD_ENCODE]   [default: "true"]` Use this when you want to use the list in JS - [example](https://github.com/toonvd/toonvd.github.io/blob/main/.templates/index.html#L100)

## Required templates + params:
- index.html:
  - `{{blogList}}`
- blogPost:
  - `{{title}}`
  - `{{image}}`
  - `{{url}}`
  - `{{basename}}`
  - `{{updated_at}}`
  - `{{html}}`
- indexBlogPost.html:
  - `{{image}}`
  - `{{basename}}`
  - `{{summary}}`
  - `{{url}}`

## Example blog
[repo](https://github.com/toonvd/toonvd.github.io) - [site](https://toonvd.github.io)
