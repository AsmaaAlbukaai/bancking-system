<?php 
namespace App\Services\Account;

use App\Repositories\AccountRepository;

class AccountService
{
    protected $repo;
    protected $numberGenerator;

    public function __construct(AccountRepository $repo, AccountNumberGeneratorService $generator)
    {
        $this->repo = $repo;
        $this->numberGenerator = $generator;
    }

    public function createAccount(array $data)
    {
        // توليد رقم الحساب
        $data['account_number'] = $this->numberGenerator->generate();
        
        // إنشاء الحساب
        return $this->repo->create($data);
    }
}
