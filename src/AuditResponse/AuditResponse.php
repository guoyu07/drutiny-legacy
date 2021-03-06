<?php

namespace Drutiny\AuditResponse;

use Drutiny\Check\Check;

/**
 * Class AuditResponse.
 *
 * @package Drutiny\AuditResponse
 */
class AuditResponse implements \JsonSerializable {

  const AUDIT_NA = -1;
  const AUDIT_SUCCESS = 0;
  const AUDIT_WARNING = 1;
  const AUDIT_FAILURE = 2;
  const AUDIT_ERROR = 3;

  protected $check;

  protected $status;

  public $exception;

  /**
   * AuditResponse constructor.
   *
   * @param \Drutiny\Check\Check $check
   *   The current check being performed.
   */
  public function __construct(Check $check) {
    $this->check = $check;
  }

  /**
   * AuditResponse to string.
   */
  public function __toString() {
    try {
      switch ($this->status) {
        case self::AUDIT_SUCCESS:
          return '<info>' . $this->getMessage('success') . '</info>';

        case self::AUDIT_NA:
          return '<info>' . $this->getMessage('na') . '</info>';

        case self::AUDIT_WARNING:
          return '<comment>' . $this->getMessage('warning') . '</comment>';

        case self::AUDIT_FAILURE:
          return '<error>' . $this->getMessage('failure') . '</error>';

        case self::AUDIT_ERROR:
        default:
          return '<error>' . $this->getMessage('exception') . '</error>';
      }
    }
    catch (\Exception $e) {
      var_dump($e->getMessage());
    }
    return 'doh';
  }

  /**
   * Used when dumping results of a site audit to JSON.
   *
   * @return array
   *   The check information, and it's associated response from the site.
   */
  public function jsonSerialize() {
    try {
      switch ($this->status) {
        case self::AUDIT_SUCCESS:
          return [
            'check' => [
              'title' => $this->getTitle(),
              'description' => strip_tags($this->getDescription()),
            ],
            'status' => 'success',
            'message' => strip_tags($this->getMessage('success')),
          ];

        case self::AUDIT_NA:
          return [
            'check' => [
              'title' => $this->getTitle(),
              'description' => strip_tags($this->getDescription()),
            ],
            'status' => 'na',
            'message' => strip_tags($this->getMessage('na')),
          ];

        case self::AUDIT_WARNING:
          return [
            'check' => [
              'title' => $this->getTitle(),
              'description' => strip_tags($this->getDescription()),
            ],
            'status' => 'warning',
            'message' => strip_tags($this->getMessage('warning')),
          ];

        case self::AUDIT_FAILURE:
          return [
            'check' => [
              'title' => $this->getTitle(),
              'description' => strip_tags($this->getDescription()),
            ],
            'status' => 'failure',
            'message' => strip_tags($this->getMessage('failure')),
          ];

        case self::AUDIT_ERROR:
        default:
          return [
            'check' => [
              'title' => $this->getTitle(),
              'description' => strip_tags($this->getDescription()),
            ],
            'status' => 'exception',
            'message' => strip_tags($this->getMessage('exception')),
          ];
      }
    }
    catch (\Exception $e) {
      var_dump($e->getMessage());
    }
    return 'doh';
  }

  /**
   * Translate a message with configured tokens.
   *
   * @param $message
   *   The message to display.
   *
   * @return string
   *   The translated string.
   */
  private function translate($message, $tokens = []) {
    $tokens = empty($tokens) ? $this->check->getTokens() : $tokens;
    return strtr($message, $tokens);
  }

  /**
   * Get a message based on the type of response.
   *
   * @param string $type
   *   The type of response.
   *
   * @return string
   *   A checks configured response.
   *
   * @throws \Exception
   */
  protected function getMessage($type = 'success') {
    switch ($type) {
      case 'success':
      case 'not_available':
      case 'warning':
      case 'failure':
      case 'exception':
      case 'notice':
        $message = $this->check->getInfo()->{$type};
        break;

      case 'na':
        return $this->getMessage('not_available');

      default:
        throw new \Exception("Cannot format message. Unknown type $type.");
    }

    $tokens = $this->check->getTokens();
    if ($type == 'exception') {
      $tokens[':exception'] = $this->exception->getMessage();
    }

    return $this->translate($message, $tokens);
  }

  /**
   * Get a translated version of the description for the check performed.
   *
   * @return string
   *   Translated description.
   */
  public function getDescription() {
    return $this->translate($this->check->getInfo()->description);
  }

  /**
   * Get a translated version of the remediation.
   *
   * @return string
   *   Translated remediation.
   */
  public function getRemediation() {
    return $this->translate($this->check->getInfo()->remediation);
  }

  /**
   * Mutator for response status.
   *
   * @param int $status
   *   Should be one of the defined constants.
   */
  public function setStatus($status) {
    $this->status = $status;
  }

  /**
   * Accessor for the response status.
   *
   * @return int
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Get a translated version of the title.
   *
   * @return string
   *   The checks title.
   */
  public function getTitle() {
    return $this->translate($this->check->getInfo()->title);
  }

}
