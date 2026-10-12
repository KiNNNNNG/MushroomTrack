<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:24px;background:#faf9f7;font-family:Arial,Helvetica,sans-serif;color:#1a1411">
  <div style="max-width:520px;margin:0 auto;background:#fff;border:1px solid #e8e6e3;border-radius:10px;padding:28px">
    <div style="font-size:22px;font-weight:800;color:#4e3b31;margin-bottom:18px">MushFung<span style="color:#c48a3a">AI</span></div>
    <p style="margin:0 0 6px;color:#8a96b0;font-size:13px">Hi <?php echo e($toName); ?>,</p>
    <h2 style="margin:0 0 10px;font-size:18px"><?php echo e($noticeTitle); ?></h2>
    <p style="margin:0 0 20px;font-size:15px;line-height:22px"><?php echo e($noticeBody); ?></p>
    <?php if($noticeLink): ?>
      <a href="<?php echo e($noticeLink); ?>" style="display:inline-block;background:#4e3b31;color:#fff;text-decoration:none;font-weight:700;font-size:13px;padding:11px 20px;border-radius:8px">OPEN MUSHFUNGAI</a>
    <?php endif; ?>
    <p style="margin:26px 0 0;color:#8a96b0;font-size:12px">You can change how you are notified under Profile &rarr; Notifications.</p>
  </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\emails\notice.blade.php ENDPATH**/ ?>