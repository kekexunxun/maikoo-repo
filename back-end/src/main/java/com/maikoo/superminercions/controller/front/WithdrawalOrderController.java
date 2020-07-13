package com.maikoo.superminercions.controller.front;

import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.model.dto.WithdrawalOrderDTO;
import com.maikoo.superminercions.model.dto.WithdrawalOrderInformationDTO;
import com.maikoo.superminercions.model.query.WithdrawalOrderQuery;
import com.maikoo.superminercions.service.WithdrawalService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@Controller
@ResponseBody
@RequestMapping("/api/withdrawal")
public class WithdrawalOrderController {
    @Autowired
    private WithdrawalService withdrawalService;

    @RequestMapping("/apply")
    public ResponseDTO withdrawal(@Validated WithdrawalOrderQuery withdrawalOrderQuery) {
        withdrawalService.withdrawal(withdrawalOrderQuery);
        return new ResponseDTO(200, "申请成功", null);
    }

    @RequestMapping("/information")
    public ResponseDTO<WithdrawalOrderInformationDTO> information(@RequestParam("listSn") long orderSN){
        return new ResponseDTO<>(200, "获取成功", withdrawalService.information(orderSN));
    }

    @RequestMapping
    public ResponseDTO<List<WithdrawalOrderDTO>> list(@RequestParam(name = "pageNum", defaultValue = "1", required = false) int pageNumber){
        return new ResponseDTO<>(200, "获取成功", withdrawalService.list(pageNumber));
    }
}
