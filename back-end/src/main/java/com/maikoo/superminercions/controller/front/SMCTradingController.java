package com.maikoo.superminercions.controller.front;

import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.model.dto.SMCFeeDTO;
import com.maikoo.superminercions.model.dto.SMCTradingDTO;
import com.maikoo.superminercions.model.dto.SMCTradingInformationDTO;
import com.maikoo.superminercions.model.query.SMCTradingQuery;
import com.maikoo.superminercions.service.SMCTradingService;
import com.maikoo.superminercions.validator.SMCBuy;
import com.maikoo.superminercions.validator.SMCSell;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import javax.validation.groups.Default;
import java.util.List;

@Controller
@RequestMapping("/api/smc/trading")
@ResponseBody
public class SMCTradingController {
    @Autowired
    private SMCTradingService smcTradingService;

    @RequestMapping("/buy")
    public ResponseDTO buy(@Validated({Default.class, SMCBuy.class}) SMCTradingQuery smcTradingQuery) {
        smcTradingService.buy(smcTradingQuery);
        return new ResponseDTO(200, "申请成功", null);
    }

    @RequestMapping("/sell")
    public ResponseDTO sell(@Validated({Default.class, SMCSell.class}) SMCTradingQuery smcTradingQuery) {
        smcTradingService.sell(smcTradingQuery);
        return new ResponseDTO(200, "申请成功", null);
    }

    @RequestMapping
    public ResponseDTO<List<SMCTradingDTO>> list(@RequestParam(name = "pageNum", defaultValue = "1", required = false) int pageNumber, @RequestParam(name = "tradeType") int type) {
        return new ResponseDTO(200, "获取成功", smcTradingService.list(pageNumber, type));
    }

    @RequestMapping("/information")
    public ResponseDTO<SMCTradingInformationDTO> information(@RequestParam(name = "listSn") long orderSN) {
        return new ResponseDTO(200, "获取成功", smcTradingService.information(orderSN));
    }

    @RequestMapping("/fee")
    public ResponseDTO<SMCFeeDTO> fee(@RequestParam(name = "rateType", defaultValue = "ALL", required = false) String type) {
        return new ResponseDTO<>(200, "获取成功", smcTradingService.fee(type));
    }
}
