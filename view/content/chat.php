<ul class="nav nav-tabs" id="tabChat" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="sup-tab" data-bs-toggle="tab" data-bs-target="#sup" type="button" role="tab" aria-controls="sup" aria-selected="true">Supervisão</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="regional-tab" data-bs-toggle="tab" data-bs-target="#regional" type="button" role="tab" aria-controls="regional" aria-selected="false">Regional</button>
  </li>
</ul>


<div class="tab-content" id="myTabContent">
  <div class="tab-pane fade show active" id="sup" role="tabpanel" aria-labelledby="sup-tab"><?php include("chat/chat.php"); ?></div>
  <div class="tab-pane fade" id="regional" role="tabpanel" aria-labelledby="regional-tab">...</div>
</div>
