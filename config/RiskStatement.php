<?php
/**
 * Created by PhpStorm.
 * User: Kana
 * Date: 2019/12/25
 * Time: 10:50
 */

return [
    'risk_statement' => [
        'cn' => [
            'title' => '用户服务协议',
            'content' => "尊敬的质子钱包用户，为给你带来更好的使用体验，请认真阅读以下用户服务协议，了解自身风险承受能力，在充分考虑后自行决定在质子钱包进行相关产品活动的服务：

1.	质子钱包推出PoS持仓生息活动，在云端钱包进行，即为质子链用户提供闲置PTT资产增值服务。用户在使用该服务时，即无条件授权质子钱包按照规则进行合理的分配和发放。

2.	云端钱包目前只接受PTT的转入转出，用户需请谨慎操作，以免误操作带来的其他资产丢失，质子钱包不承担任何责任。

3.	用户在使用该项服务过程中，应当严格遵守国家相关法律，保证资产的来源合法合规。

4.	加密虚拟货币的增值或者贬值情况波动较大，用户应当充分认识到其投资的风险，谨慎操作，量力而行。

5.	由于网络延迟，系统故障、黑客攻击及其他可能的不可抗拒因素，可能导致服务过程中的执行迟缓，暂停，偏差，终止等情况，质子钱包将尽力保证但不承诺服务执行系统运行的完全稳定，但由于上述因素导致的最终执行结果与用户预期不同，质子钱包不承担任何责任。

6.	用户应妥善保管好质子钱包中云端钱包所有的账号密码等与资产安全相关的信息，如丢失或遗忘，用户需自行承担其带来的后果。

7.	质子钱包保留对暂停、终止PoS服务管理的权限，在必要的时候，质子钱包可以随时暂停，终止PoS持仓增值的服务。

8.	用户如在提币、转账过程中如因其自身错误操作而导致资产丢失，质子钱包不承担任何责任。

9.	云端钱包在转账过程中，会收取合理数量的PTT作为服务费，因为PTT是基于erc-20体系，每一笔转账需要向以太坊网络缴纳矿工费。

10.	用户同意在质子钱包所进行的所有操作代表其真实的意愿，并无条件接受该决策及操作所带来的潜在风险。

特别提醒：用户点击同意该《用户服务协议》，则表示同意参与质子钱包中云端钱包的 PoS 持仓活动服务，并已经知晓且自行承担所有风险和损失。"
        ],
        'en' => [
            'title' => 'User Service Agreement',
            'content' => "Dear Proton Wallet users, in order to bring you better user experience, please carefully read the following user service agreement, learn about your own risk tolerance, and decide at discretion on the services for related product activities in Proton Wallet after full consideration: 

1. Proton Wallet launches PoS-based incentive activities, which are carried out in Cloud Wallet, i.e., provide idle PTT asset incentive activities for Proton chain users. When users use this service, they unconditionally authorize Proton Wallet to carry out reasonable allocation and distribution according to the rules. 

2. Cloud Wallet only accepts PTT transfer-in and transfer-out at present. The users should be prudent in operation to avoid loss of other assets caused by misoperation. Proton Wallet will not assume any liability. 

3. Users shall strictly abide by relevant national laws in the process of using this service to ensure the legality and compliance of the source of assets.

4. The appreciation or depreciation of encrypted virtual currency fluctuates greatly. The users should fully realize the risks of their investment, operate cautiously and do what they can. 

5. In case of network delay, system failure, hacker attack and other possible irresistible factors, which may result in the delay, suspension, deviation, termination, etc. of the execution in the service process, Proton Wallet will try its best to ensure, but not promise, completely stable operation of the service execution system. However, Proton Wallet will not bear any liability for the difference between the final execution result arising from the above factors and the user expectation. 

6. Users should properly keep all account numbers, passwords and other information related to asset security in Cloud Wallet in the Proton Wallet. If lost or forgotten, the user should bear the corresponding consequences. 

7. Proton Wallet reserves the right to suspend or terminate PoS service management. When necessary, Proton Wallet can suspend and terminate PoS-based incentive activities at any time.

8. Proton Wallet will not bear any liability for the loss of assets caused by the user’s false operation during the token withdrawal and transfer process. 

9. During the transfer process of Cloud Wallet, a reasonable amount of PTT will be charged as service fee. Since PTT is based on erc-20 system, miner’s fee should be paid to Ethernet for each transfer. 

10. The users agree that all operations conducted in Proton Wallet represent their true attentions and unconditionally accept the potential risks brought by the decision and operations. 

Special tip: if the users click to agree with the User Service Agreement, it means that they agree to participate in PoS-based incentive activities of Cloud Wallet in the Proton Wallet, and have already known and will bear all risks and losses on their own."
        ],
    ],
    'reward_interrupt_instruction' => [
        'cn' => [
            'title' => '奖励中断说明',
            'content' => "1. 参加活动后，点击「取消持仓」，之后再次报名，再次发放奖励日期为再次报名起第三日。

2. 参加活动后，检测到云端钱包余额小于活动起投量。之后充值余额又大于起投量，再次发放奖励日期为大于起投量起第三日。"
        ],
        'en' => [
            'title' => 'PoS Yield Resettlement Rules',
            'content' => "1. For participated users, if one clicks 'Quit PoS', then clicks 'Sign Up' again. The yield resettlement is 2 days after you re-join in the PoS.

2. For participated users, if detected wallet balance is lower than the minimum requirement, then user recharges wallet and makes the balance meet the requirement again, the yield resettlement will be 2 days after.
"
        ],
    ]
];