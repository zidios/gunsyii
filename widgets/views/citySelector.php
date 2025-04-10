<!-- Модальное окно выбора города -->
<div class="modal fade" id="cityModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Выберите ваш город</h5>
            </div>
            <div class="modal-body text-center">
                <?php foreach ($cities as $city): ?>
                    <button type="button" class="btn btn-outline-secondary city-btn m-2" data-city="<?= $city ?>">
                        <?= $city ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>