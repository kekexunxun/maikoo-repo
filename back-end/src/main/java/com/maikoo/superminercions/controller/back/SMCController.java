package com.maikoo.superminercions.controller.back;

import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.model.dto.SMCLockWithCustomerDTO;
import com.maikoo.superminercions.model.dto.SMCTradingWithCustomerDTO;
import com.maikoo.superminercions.model.dto.SMCWithdrawalWithCustomerDTO;
import com.maikoo.superminercions.model.query.OrderQuery;
import com.maikoo.superminercions.service.SMCLockService;
import com.maikoo.superminercions.service.SMCTradingService;
import com.maikoo.superminercions.service.WithdrawalService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@Controller
@ResponseBody
@RequestMapping("/api/admin/smc")
public class SMCController {
    @Autowired
    private SMCTradingService smcTradingService;
    @Autowired
    private WithdrawalService withdrawalService;
    @Autowired
    private SMCLockService smcLockService;

    @RequestMapping("/buy")
    public ResponseDTO<List<SMCTradingWithCustomerDTO>> buyList() {
        return new ResponseDTO(200, "获取成功", smcTradingService.listAll(0));
    }

    @RequestMapping("/sell")
    public ResponseDTO<List<SMCTradingWithCustomerDTO>> sellList() {
        return new ResponseDTO(200, "获取成功", smcTradingService.listAll(1));
    }

    @RequestMapping("/buy/update")
    public ResponseDTO buyUpdate(OrderQuery orderQuery) {
        smcTradingService.updateBuyStatus(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/buy/remark/update")
    public ResponseDTO buyNoteUpdate(OrderQuery orderQuery) {
        smcTradingService.updateBuyNote(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/sell/update")
    public ResponseDTO sellUpdate(OrderQuery orderQuery) {
        smcTradingService.updateSellStatus(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/sell/remark/update")
    public ResponseDTO sellNoteUpdate(OrderQuery orderQuery) {
        smcTradingService.updateSellNote(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/withdrawal")
    public ResponseDTO<List<SMCWithdrawalWithCustomerDTO>> withdrawalList(){
        return new ResponseDTO<>(200, "获取成功", withdrawalService.listAll());
    }

    @RequestMapping("/withdrawal/update")
    public ResponseDTO withdrawalUpdate(OrderQuery orderQuery){
        smcTradingService.updateWithdrawalStatus(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/withdrawal/remark/update")
    public ResponseDTO withdrawalNoteUpdate(OrderQuery orderQuery){
        smcTradingService.updateWithdrawalNote(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/lock")
    public ResponseDTO<List<SMCLockWithCustomerDTO>> lockList(){
        return new ResponseDTO<>(200, "获取成功", smcLockService.listAll());
    }

    @RequestMapping("/lock/update")
    public ResponseDTO lockUpdate(OrderQuery orderQuery){
        smcTradingService.updateLockStatus(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }
}

