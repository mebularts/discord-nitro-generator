
<?php ob_start(); ?>
<article class="tw-bg-white tw-border tw-rounded-2xl tw-p-6">
  <h1 class="tw-text-2xl tw-font-semibold tw-mb-2"><?= h($riddle['title']) ?></h1>
  <div class="tw-text-lg tw-mb-4"><?= nl2br(h($riddle['body'])) ?></div>
  <button id="revealBtn" class="btn btn-primary">Show me the answer</button>
  <div id="answer" class="tw-mt-3 tw-hidden tw-p-4 tw-border tw-rounded-2xl tw-bg-gray-50"><?= nl2br(h($riddle['answer'])) ?></div>

  <div class="tw-mt-6 tw-flex tw-items-center tw-gap-2">
    <button data-vote="up"   class="btn btn-outline-success btn-sm">👍</button>
    <button data-vote="down" class="btn btn-outline-danger btn-sm">👎</button>
    <span id="score" class="tw-ml-2 tw-text-sm tw-text-gray-600">
      <?php $t=(int)$riddle['up_votes']+(int)$riddle['down_votes']; echo $t>0? round(100*$riddle['up_votes']/max(1,$t),2).' %':'—'; ?>
    </span>
  </div>
</article>

<script>
document.getElementById('revealBtn').onclick=()=>document.getElementById('answer').classList.toggle('tw-hidden');
document.querySelectorAll('[data-vote]').forEach(btn=>{
  btn.onclick=async ()=>{
    const dir=btn.dataset.vote==='up'?1:-1;
    const res=await fetch('/api/vote',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:<?= (int)$riddle['id']?>,dir})});
    const json=await res.json();
    if(json.ok){ document.getElementById('score').textContent=json.percent+' %'; }
  };
});
</script>
<?php $content = ob_get_clean(); include __DIR__.'/../layouts/base.php'; ?>
