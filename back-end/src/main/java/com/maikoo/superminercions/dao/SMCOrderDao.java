package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.SMCOrderDO;
import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Mapper;
import org.apache.ibatis.annotations.Param;

import java.util.List;

@Mapper
public interface SMCOrderDao {
    @Insert("INSERT " +
            "INTO " +
                "c_order_smc" +
                "(c_order_id, quantity, price, buying_price, fee, type) " +
            "VALUES" +
                "(#{id}, #{quantity}, #{price}, #{buyingPrice}, #{fee}, #{type})")
    int insert(SMCOrderDO smcOrderDO);

    List<Long> selectPageIds(@Param("customerUserId") long customerUserId, @Param("type") int type);

    SMCOrderDO selectOne(long orderSN);

    List<SMCOrderDO> selectByIds(@Param("pageIds") List<Long> pageIds);

    List<SMCOrderDO> selectWithCustomerByIds(@Param("pageIds") List<Long> pageIds);

    List<SMCOrderDO> selectByType(int type);
}
