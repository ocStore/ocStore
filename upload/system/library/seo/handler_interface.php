<?php
/**
 * @package   SeoPro
 * @copyright Copyright (c) 2026, ocStore (https://ocstore.com/)
 * @license   https://opensource.org/licenses/GPL-3.0
 */
namespace Opencart\System\Library\Seo;

interface HandlerInterface {
	/**
	 * Ключі запиту, за які відповідає обробник, наприклад ['product_id'].
	 *
	 * @return array<int, string>
	 */
	public function getKeys(): array;

	/**
	 * Маршрут, який обробник виставляє та обслуговує.
	 */
	public function getRoute(): string;

	/**
	 * Адреса → запит. Отримує вже розібрані ключі, повертає доповнений
	 * масив із ключем route або null, якщо адреса не належить обробнику.
	 *
	 * @param array<string, mixed> $get
	 *
	 * @return array<string, mixed>|null
	 */
	public function decode(array $get): ?array;

	/**
	 * Запит → адреса. Повертає масив із трьома ключами або null:
	 *   queries — рядки виду 'product_id=42' у порядку сегментів;
	 *   data    — те, що лишається в query string;
	 *   postfix — чи додавати закінчення адреси.
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array<string, mixed>|null
	 */
	public function encode(array $data): ?array;
}
