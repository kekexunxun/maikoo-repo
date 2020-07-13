package com.maikoo.superminercions.controller.front;

import com.maikoo.superminercions.model.dto.*;
import com.maikoo.superminercions.model.query.ETHSellQuery;
import com.maikoo.superminercions.model.query.ETHSwapSMCQuery;
import com.maikoo.superminercions.model.query.ETHWithdrawalQuery;
import com.maikoo.superminercions.service.ETHService;
import com.maikoo.superminercions.validator.ETHSwapSMC;
import com.maikoo.superminercions.validator.SMCSwapETH;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import javax.validation.groups.Default;
import java.util.List;

@Controller
@ResponseBody
@RequestMapping("/api/eth")
public class ETHController {
    @Autowired
    private ETHService ethService;

    @RequestMapping("/withdrawal/apply")
    public ResponseDTO applyWithdrawal(@Validated ETHWithdrawalQuery ethWithdrawalQuery) {
        ethService.applyWithdrawal(ethWithdrawalQuery);
        return new ResponseDTO(200, "申请成功", null);
    }

    @RequestMapping("/withdrawal")
    public ResponseDTO<List<ETHWithdrawalDTO>> withdrawalList(@RequestParam(name = "pageNum", defaultValue = "1", required = false) int pageNumber) {
        return new ResponseDTO<>(200, "获取成功", ethService.withdrawalList(pageNumber));
    }

    @RequestMapping("/withdrawal/information")
    public ResponseDTO<ETHWithdrawalInformationDTO> withdrawalInformation(@RequestParam(name = "listSn") long orderSN) {
        return new ResponseDTO<>(200, "获取成功", ethService.withdrawalInformation(orderSN));
    }

    @RequestMapping("/smc/exchange")
    public ResponseDTO exchangeSMC(@Validated({Default.class, ETHSwapSMC.class}) ETHSwapSMCQuery ethSwapSMCQuery) {
        ethService.exchangeSMC(ethSwapSMCQuery);
        return new ResponseDTO(200, "申请成功", null);
    }

    @RequestMapping("/smc")
    public ResponseDTO<List<ETHExchangeSMCDTO>> exchangeSMCList(@RequestParam(name = "pageNum", defaultValue = "1", required = false) int pageNumber) {
        return new ResponseDTO<>(200, "获取成功", ethService.exchangeSMCList(pageNumber));
    }

    @RequestMapping("/smc/information")
    public ResponseDTO<ETHExchangeSMCInformationDTO> exchangeSMCInformation(@RequestParam(name = "listSn") long orderSN) {
        return new ResponseDTO<>(200, "获取成功", ethService.exchangeSMCInformation(orderSN));
    }

    @RequestMapping("/buy/apply")
    public ResponseDTO buy(@Validated({Default.class, SMCSwapETH.class}) ETHSwapSMCQuery ethSwapSMCQuery){
        ethService.buy(ethSwapSMCQuery);
        return new ResponseDTO(200, "申请成功", null);
    }

    @RequestMapping("/buy")
    public ResponseDTO<List<ETHBuyDTO>> buyList(@RequestParam(name = "pageNum", defaultValue = "1", required = false) int pageNumber){
        return new ResponseDTO<>(200, "获取成功", ethService.buyList(pageNumber));
    }

    @RequestMapping("/buy/information")
    public ResponseDTO<ETHBuyInformationDTO> buyInformation(@RequestParam(name = "listSn") long orderSN){
        return new ResponseDTO<>(200, "获取成功", ethService.buyInformation(orderSN));
    }

    @RequestMapping("/sell/apply")
    public ResponseDTO sell(@Validated ETHSellQuery ethSellQuery){
        ethService.sell(ethSellQuery);
        return new ResponseDTO(200, "申请成功", null);
    }

    @RequestMapping("/sell")
    public ResponseDTO<List<ETHSellDTO>> sellList(@RequestParam(name = "pageNum", defaultValue = "1", required = false) int pageNumber){
        return new ResponseDTO<>(200, "获取成功", ethService.sellList(pageNumber));
    }

    @RequestMapping("/sell/information")
    public ResponseDTO<ETHSellInformationDTO> sellInformation(@RequestParam(name = "listSn") long orderSN){
        return new ResponseDTO<>(200, "获取成功", ethService.sellInformation(orderSN));
    }
}
