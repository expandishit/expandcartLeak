<?php
class ModelExtensionPaymentMethod extends Model
{
    /**
     * @var string
     */
    protected $table = 'payment_methods';

    /**
     * Select payment method useing it's code.
     * this method select the payment method from the master database.
     *
     * @param string $code
     * @param array $columns 
     *
     * @return array|bool
     */
    public function selectByCode(string $code, array $columns = ['*'])
    {
        $query = [];
        $query[] = 'SELECT %s FROM %s WHERE `code`="%s"';
        $data = $this->ecusersdb->query(vsprintf(implode(' ', $query), [
            implode(',', $columns),
            $this->table,
            $code
        ]));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }
}
