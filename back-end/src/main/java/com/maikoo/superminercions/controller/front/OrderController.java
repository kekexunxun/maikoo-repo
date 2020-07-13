package com.maikoo.superminercions.controller.front;

import com.maikoo.superminercions.model.CustomerDO;
import com.maikoo.superminercions.model.dto.OrderCountDTO;
import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.service.OrderService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

import javax.servlet.http.HttpSession;

@Controller
@ResponseBody
@RequestMapping("/api/order")
public class OrderController {
    @Autowired
    private OrderService orderService;

    @RequestMapping("/count")
    public ResponseDTO<OrderCountDTO> getCountUserOrder(HttpSession httpSession){
        CustomerDO customerDO = (CustomerDO)httpSession.getAttribute("current_customer");
        OrderCountDTO orderCountDTO =new OrderCountDTO();
        orderCountDTO.setTransCount(orderService.countOrderByUser(customerDO.getId()));
        return new ResponseDTO(200, "获取成功", orderCountDTO);
    }
}
