<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div class="mt-4 rounded-md border border-green-600 bg-green-50 px-4 py-3 text-green-700" role="alert">
        <p class="text-sm font-medium"><?php echo e(session('success')); ?></p>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
    <div class="mt-4 rounded-md border border-[#CC0000] bg-[#CC0000]/10 px-4 py-3 text-[#CC0000]" role="alert">
        <p class="text-sm font-medium"><?php echo e(session('error')); ?></p>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\coopSistema\resources\views/components/ui/alert.blade.php ENDPATH**/ ?>