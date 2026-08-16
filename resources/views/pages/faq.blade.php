@extends('layouts.app')

@section('title', 'FAQ | JobBoardSoftware')
@section('meta_description', 'Frequently asked questions about JobBoardSoftware.')

@section('content')
<style>
.faq-block {
  width: 900px;
  max-width: 100%;
  margin: 48px auto;
  padding: 0 24px;
}

.faq-item {
  background: #ffffff;
  border: 1px solid #dedede;
  border-radius: 5px;
  margin-bottom: 12px;
}

.faq-item + .faq-item {
  margin-top: 0;
}

.faq-item summary {
  padding: 18px 20px;
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  cursor: pointer;
  list-style: none;
}

.faq-item summary::-webkit-details-marker {
  display: none;
}

.faq-item summary::marker {
  content: '';
}

.faq-item__answer {
  margin: 0;
  padding: 0 20px 18px;
  color: var(--color-text-muted, #555);
  font-family: 'Poppins', sans-serif;
  line-height: 1.6;
}
</style>

<div class="faq-block">
  <div class="faq-item">
    <details>
      <summary>How quickly can I launch my job board?</summary>
      <p class="faq-item__answer">Most job boards go live within minutes. Create your environment, add your first vacancies, and your site is available on a free jobboardsoftware.co subdomain right away.</p>
    </details>
  </div>

  <div class="faq-item">
    <details>
      <summary>Can I use my own domain?</summary>
      <p class="faq-item__answer">Yes. Connect a domain you already own and we'll verify your DNS records and activate SSL automatically once it's set up.</p>
    </details>
  </div>

  <div class="faq-item">
    <details>
      <summary>What happens after my free trial ends?</summary>
      <p class="faq-item__answer">You can choose a plan that fits your job board at any time. If you don't upgrade, your job board simply stays on the free trial until you do.</p>
    </details>
  </div>
</div>
@endsection
