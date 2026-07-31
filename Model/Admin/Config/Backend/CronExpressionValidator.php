<?php

namespace Wizzy\Search\Model\Admin\Config\Backend;

use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class CronExpressionValidator extends Value
{
    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ScheduleFactory $scheduleFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ScheduleFactory $scheduleFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->scheduleFactory = $scheduleFactory;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Validate cron expression before saving configuration value.
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = trim((string)$this->getValue());

        if ($value === '') {
            throw new LocalizedException(__('Cron expression cannot be empty.'));
        }

        try {
            $this->validateCronExpression($value);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Invalid cron expression. Please enter a valid 5-field cron schedule.')
            );
        }

        return parent::beforeSave();
    }

    /**
     * Validate cron expression in a Magento-version-safe way.
     *
     * Magento has used multiple validators across versions:
     * - Magento\Framework\Stdlib\DateTime\Cron\CronExpression (some versions)
     * - Cron\CronExpression (if the cron-expression library is installed)
     * - Magento\Cron\Model\Schedule via ScheduleFactory (common in 2.4.x)
     *
     * @param string $expr
     * @return void
     * @throws \Exception
     */
    private function validateCronExpression(string $expr): void
    {
        $parts = \preg_split('#\s+#', \trim($expr), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (\count($parts) !== 5) {
            throw new \InvalidArgumentException('Cron expression must have 5 fields.');
        }

        // 1) Magento wrapper (if present)
        if (\class_exists(\Magento\Framework\Stdlib\DateTime\Cron\CronExpression::class)) {
            \Magento\Framework\Stdlib\DateTime\Cron\CronExpression::factory($expr);
            // Some validators may accept out-of-range values; enforce ranges ourselves.
            $this->validateCronExpressionRanges($parts);
            return;
        }

        // 2) Library validator (if installed)
        if (\class_exists(\Cron\CronExpression::class)) {
            \Cron\CronExpression::factory($expr);
            $this->validateCronExpressionRanges($parts);
            return;
        }

        // 3) Magento cron Schedule validator
        $schedule = $this->scheduleFactory->create();
        $schedule->setCronExpr($expr);

        // Force syntax validation per-field (e.g. reject "* a * * *")
        // We don't care if it "matches", only that it parses without exception.
        $schedule->matchCronExpression($parts[0], 0); // minute: 0-59
        $schedule->matchCronExpression($parts[1], 0); // hour: 0-23
        $schedule->matchCronExpression($parts[2], 1); // day: 1-31
        $schedule->matchCronExpression($parts[3], 1); // month: 1-12
        $schedule->matchCronExpression($parts[4], 0); // weekday: 0-6

        // Range validation (Magento matchCronExpression can "return false" for out-of-range
        // numbers instead of throwing, which would allow a broken schedule to be saved).
        $this->validateCronExpressionRanges($parts);
    }

    /**
     * Enforce numeric ranges for all cron fields.
     *
     * This prevents schedules that are syntactically valid but will never run,
     * e.g. "99 * * * *" (minute out of range).
     *
     * Ranges follow Magento's cron matcher expectations:
     * - minute: 0-59
     * - hour: 0-23
     * - day of month: 1-31
     * - month: 1-12 (or jan-dec)
     * - day of week: 0-6 (or sun-sat)
     *
     * @param string[] $parts
     * @return void
     */
    private function validateCronExpressionRanges(array $parts): void
    {
        $monthNames = [
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'may' => 5,
            'jun' => 6,
            'jul' => 7,
            'aug' => 8,
            'sep' => 9,
            'oct' => 10,
            'nov' => 11,
            'dec' => 12,
        ];
        $dowNames = [
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
        ];

        $this->validateCronFieldRanges($parts[0], 0, 59, false, []);
        $this->validateCronFieldRanges($parts[1], 0, 23, false, []);
        $this->validateCronFieldRanges($parts[2], 1, 31, false, []);
        $this->validateCronFieldRanges($parts[3], 1, 12, true, $monthNames);
        $this->validateCronFieldRanges($parts[4], 0, 6, true, $dowNames);
    }

    /**
     * Validate ranges and optional names for a single cron field.
     *
     * @param string $field
     * @param int $min
     * @param int $max
     * @param bool $allowNames
     * @param array $nameMap map of 3-letter name => int
     * @return void
     */
    private function validateCronFieldRanges(string $field, int $min, int $max, bool $allowNames, array $nameMap): void
    {
        foreach (\explode(',', $field) as $token) {
            $token = \trim($token);
            if ($token === '') {
                throw new \InvalidArgumentException('Cron expression contains an empty token.');
            }

            // base/step
            $step = null;
            if (\strpos($token, '/') !== false) {
                [$base, $step] = \explode('/', $token, 2);
                $base = \trim($base);
                $step = \trim($step);
                if ($base === '' || $step === '' || !\ctype_digit($step) || (int)$step < 1) {
                    throw new \InvalidArgumentException('Cron expression contains an invalid step value.');
                }
                $token = $base;
            }

            if ($token === '*') {
                continue;
            }

            if (\strpos($token, '-') !== false) {
                [$fromRaw, $toRaw] = \explode('-', $token, 2);
                $from = $this->toCronNumber(\trim($fromRaw), $allowNames, $nameMap);
                $to = $this->toCronNumber(\trim($toRaw), $allowNames, $nameMap);
                if ($from < $min || $from > $max || $to < $min || $to > $max) {
                    throw new \InvalidArgumentException('Cron expression contains an out-of-range value.');
                }
                if ($from > $to) {
                    throw new \InvalidArgumentException('Cron expression contains an invalid range (from > to).');
                }
                continue;
            }

            $value = $this->toCronNumber($token, $allowNames, $nameMap);
            if ($value < $min || $value > $max) {
                throw new \InvalidArgumentException('Cron expression contains an out-of-range value.');
            }
        }
    }

    /**
     * Convert a cron token to a numeric value (handles month/day names).
     *
     * @param string $value
     * @param bool $allowNames
     * @param array $nameMap
     * @return int
     */
    private function toCronNumber(string $value, bool $allowNames, array $nameMap): int
    {
        $value = \trim($value);
        if ($value === '' || $value === '*') {
            throw new \InvalidArgumentException('Cron expression contains an invalid token.');
        }
        if (\ctype_digit($value)) {
            return (int)$value;
        }
        if ($allowNames) {
            $key = \strtolower(\substr($value, 0, 3));
            if (isset($nameMap[$key])) {
                return (int)$nameMap[$key];
            }
        }
        throw new \InvalidArgumentException('Cron expression contains an invalid token.');
    }
}
