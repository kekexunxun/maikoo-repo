package com.maikoo.superminercions.controller.back;

import com.maikoo.superminercions.model.dto.*;
import com.maikoo.superminercions.model.query.OrderQuery;
import com.maikoo.superminercions.service.ETHService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@Controller("AdminETHController")
@ResponseBody
@RequestMapping("/api/admin/eth")
public class ETHController {
    @Autowired
    private ETHService ethService;

    @RequestMapping("/buy")
    public ResponseDTO<List<ETHBuyWithCustomerDTO>> buyList(){
        return new ResponseDTO<>(200, "获取成功", ethService.buyListAll());
    }

    @RequestMapping("/buy/update")
    public ResponseDTO buyUpdate(OrderQuery orderQuery){
        ethService.updateBuyStatus(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/buy/remark/update")
    public ResponseDTO buyNoteUpdate(OrderQuery orderQuery){
        ethService.updateBuyNote(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/sell")
    public ResponseDTO<List<ETHSellWithCustomerDTO>> sellList(){
        return new ResponseDTO<>(200, "获取成功", ethService.sellListAll());
    }

    @RequestMapping("/sell/update")
    public ResponseDTO sellUpdate(OrderQuery orderQuery){
        ethService.updateSellStatus(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/sell/remark/update")
    public ResponseDTO sellNoteUpdate(OrderQuery orderQuery){
        ethService.updateSellNote(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/withdrawal")
    public ResponseDTO<List<ETHWithdrawalWithCustomerDTO>> withdrawalList(){
        return new ResponseDTO<>(200, "获取成功", ethService.withdrawalListAll());
    }

    @RequestMapping("/withdrawal/update")
    public ResponseDTO withdrawalUpdate(OrderQuery orderQuery){
        ethService.updateWithdrawalStatus(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/withdrawal/remark/update")
    public ResponseDTO withdrawalNoteUpdate(OrderQuery orderQuery){
        ethService.updateWithdrawalNote(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/exchange/smc")
    public ResponseDTO<List<ETHExchangeSMCWithCustomerDTO>> exchangeSMC(){
        return new ResponseDTO<>(200, "获取成功", ethService.exchangeSMCListAll());
    }

    @RequestMapping("/exchange/smc/update")
    public ResponseDTO exchangeSMCUpdate(OrderQuery orderQuery){
        ethService.updateExchangeSMCStatus(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/exchange/smc/remark/update")
    public ResponseDTO exchangeSMCNoteUpdate(OrderQuery orderQuery){
        ethService.updateExchangeSMCNote(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }
}
