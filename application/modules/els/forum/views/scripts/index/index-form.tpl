
<div class="forum forum-index">
    <?php if (!empty($this->formSection)): ?>
    <div><?= $this->formSection ?></div>
    <?php elseif (!empty($this->formMessage)): ?>
    <?php elseif (!empty($this->formTheme)): ?>
    <div class="topic-createeditor"><?= $this->formTheme ?></div>
    <?php endif; ?>
</div>