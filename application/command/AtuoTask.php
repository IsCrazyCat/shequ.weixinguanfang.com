<?php
/**
 * Created by PhpStorm.
 * configure函数是在命令行中用list命令列出所有任务的时候回显示的出的提示，
 * execute函数是说要执行的命令，在这里可以直接调用其他函数，完成例如统计等任务工作，然后用output输出到命令行
 * User: lishibo
 * Date: 2019/2/28
 * Time: 15:21
 */

namespace app\command;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Log;

class AtuoTask extends Command
{
    protected function configure(){
        $this->setName('whyyy')->setDescription("SANYU Automated Task");
    }
    protected function execute(Input $input, Output $output){
        $output->writeln('SANYU Automated Task Start ...');
        $this->ManualSeparation();
        $output->writeln('SANYU Automated Task End...');
    }
    private function ManualSeparation(){
        if(file_exists(APP_PATH.'common/logic/DistributLogicSY.php')){
            $distributLogic = new \app\common\logic\DistributLogicSY();
            $distributLogic->auto_confirm();
        }
      // Log::write("三鱼工坊自动分成，稳定后将关闭日志".date('Y-m-d H:i:s'),true);
        echo "CONSOLE: 三鱼工坊自动分成".date('Y-m-d H:i:s')."\r\n";
    }
}