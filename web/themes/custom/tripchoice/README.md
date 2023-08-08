# 📖 Boilerplate theme guide

## 🚀 Quick start
```
nvm use
npm install
npm run build
npm run watch
```
**Within the Drupal Base Project these commands can be run with ddev as well**

## 🏗️ Grid
We are using the Bootstrap grid. It is installed with npm and added to the theme by including it in src/grid/grid.scss.
It is possible to adjust te grid to your likings using the variables provided in bootstrap. As an example they are included as a comment in the grid.scss file.

For more information about using it see the [Bootstrap grid documentation](https://getbootstrap.com/docs/5.2/layout/grid/).

**Be wary that updating bootstrap to another major version can result in a broken layout.**

## 🧩 Components
We use components and we prepare our code for implementation with the SDC module.
Components are reusable pieces of code that consist of:
- twig template
- scss
- css (compiled from scss)
- js
- yml (usable for storybook and sdc implementation later on)

### Title Component
```php
{% include '@boilerplate_theme/components/title/title.twig' with {
  'label' : paragraph.field_title.value
} %}
```

## 🎨 BEM
We name our css classes with the BEM approach in mind.
```scss
.opinions_box {
    margin: 0 0 8px 0;
    text-align: center;

    &__view-more {
        text-decoration: underline;
    }

    &__text-input {
        border: 1px solid #ccc;
    }

    &--is-inactive {
        color: gray;
    }
}
```
[More documenatation about BEM here](https://getbem.com/)


## 🏳️ Fontawesome
It is possible to use Fontawesome Free in this theme.
This needs to be enabled in the theme settings (admin/appearance/settings/boilerplate_theme).

Using fontawesome the default way:
```html
<i class="fa-solid fa-ghost"></i>
```

Example of using fontawesome with [pseudoelements](https://fontawesome.com/docs/web/add-icons/pseudo-elements)
```scss
.ghost::before {
  font: var(--fa-font-solid);
  content: "\f6e2";
}

```
[Fontawesome documentation can be found here](https://fontawesome.com/docs)

## 👷🏻‍♀️ ToDo
- Fixes for Deprecation warnings in stylelinter.
- - The "color-hex-case" rule is deprecated.
- - The "string-quotes" rule is deprecated.
- Component examples
- -  Add link component.
- - News paragraph?
- - News overview
- Rename files/folder script?
- check if normalize.css is still something we need to use
- config files
- - colors primary,secondary,tertiary
- - fonts primary/secondary
- - Other config needed? spacing, mediaqueries (usable from bootstrap grid?))

