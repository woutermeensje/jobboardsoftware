@extends('layouts.app')

@section('title', 'Job alerts | JobBoardSoftware')
@section('meta_description', 'Set up job alerts for new roles in your field, region and preferred employment type.')

@section('content')
<section class="job-alerts">
  <div class="job-alerts__box">
    <h1 class="job-alerts__title">Create a job alert</h1>

    <form class="content-form" method="GET" action="{{ route('register.jobseeker') }}">
      <div class="content-form__grid">
        <div class="content-field">
          <label for="alert-firstname">First name</label>
          <input id="alert-firstname" name="firstname" type="text" placeholder="First name">
        </div>
        <div class="content-field">
          <label for="alert-email">Email address</label>
          <input id="alert-email" name="email" type="email" placeholder="you@example.com">
        </div>
      </div>

      <div class="content-field">
        <label for="alert-categories-input">Interests</label>

        <select id="alert-categories" name="categories[]" multiple class="sr-only" data-multiselect-source>
          <option value="Nature">Nature</option>
          <option value="Ecology">Ecology</option>
        </select>

        <div class="js-multiselect-field" data-multiselect-for="alert-categories">
          <div class="js-multiselect-control">
            <input type="text" id="alert-categories-input" class="js-multiselect-input" autocomplete="off" placeholder="Choose interests...">
          </div>
          <div class="js-multiselect-dropdown" hidden></div>
        </div>
      </div>

      <div class="content-actions">
        <button class="content-btn content-btn--primary" type="submit">Create account</button>
      </div>
    </form>
  </div>
</section>
@endsection

@push('styles')
  <style>
    .job-alerts {
      display: grid;
      justify-content: center;
      padding: 0 24px;
      margin: 56px 0;
    }

    .job-alerts__box {
      width: min(560px, 100%);
      padding: 30px;
      border: 1px solid var(--color-border);
      border-radius: 8px;
      background: #ffffff;
      box-shadow: var(--shadow-sm);
    }

    .job-alerts__title {
      margin: 0 0 18px;
      font-size: 24px;
      font-weight: 800;
    }
  </style>
@endpush

@push('scripts')
  <script>
    (function () {
      document.querySelectorAll('[data-multiselect-for]').forEach(function (field) {
        var select = document.getElementById(field.getAttribute('data-multiselect-for'));
        if (!select) return;

        var control = field.querySelector('.js-multiselect-control');
        var input = field.querySelector('.js-multiselect-input');
        var dropdown = field.querySelector('.js-multiselect-dropdown');

        var options = Array.prototype.map.call(select.options, function (option) {
          return { value: option.value, label: option.textContent, el: option };
        });

        function renderTags() {
          control.querySelectorAll('.js-multiselect-tag').forEach(function (tag) { tag.remove(); });

          options.filter(function (option) { return option.el.selected; }).forEach(function (option) {
            var tag = document.createElement('span');
            tag.className = 'js-multiselect-tag';
            tag.textContent = option.label;

            var remove = document.createElement('button');
            remove.type = 'button';
            remove.setAttribute('aria-label', 'Remove ' + option.label);
            remove.style.cssText = 'border:0;background:none;padding:0;margin-left:2px;cursor:pointer;line-height:1;color:inherit;font-size:14px;';
            remove.textContent = '×';
            remove.addEventListener('click', function (event) {
              event.stopPropagation();
              option.el.selected = false;
              renderTags();
              renderOptions();
            });

            tag.appendChild(remove);
            control.insertBefore(tag, input);
          });
        }

        function renderOptions(filter) {
          dropdown.innerHTML = '';
          var term = (filter || '').trim().toLowerCase();

          options
            .filter(function (option) { return !term || option.label.toLowerCase().indexOf(term) !== -1; })
            .forEach(function (option) {
              var item = document.createElement('div');
              item.className = 'js-multiselect-option' + (option.el.selected ? ' is-selected' : '');
              item.textContent = option.label;
              item.addEventListener('click', function () {
                option.el.selected = !option.el.selected;
                input.value = '';
                renderTags();
                renderOptions();
                input.focus();
              });
              dropdown.appendChild(item);
            });
        }

        function openDropdown() {
          dropdown.hidden = false;
        }

        function closeDropdown() {
          dropdown.hidden = true;
        }

        control.addEventListener('click', function () {
          input.focus();
          openDropdown();
        });

        input.addEventListener('focus', openDropdown);

        input.addEventListener('input', function () {
          renderOptions(input.value);
          openDropdown();
        });

        document.addEventListener('click', function (event) {
          if (!field.contains(event.target)) closeDropdown();
        });

        field.addEventListener('keydown', function (event) {
          if (event.key === 'Escape') closeDropdown();
        });

        renderTags();
        renderOptions();
      });
    })();
  </script>
@endpush
