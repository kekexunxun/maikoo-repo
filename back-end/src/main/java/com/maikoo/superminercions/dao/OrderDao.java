package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.OrderDO;
import org.apache.ibatis.annotations.*;

@Mapper
public interface OrderDao {

    @Insert("INSERT " +
            "INTO " +
                "c_order" +
                "(order_sn, c_user_id, status, created_at) " +
            "VALUES" +
                "(#{orderSN}, #{customerDO.id}, #{status}, UNIX_TIMESTAMP(NOW()))")
    @Options(useGeneratedKeys = true)
    int insert(OrderDO orderDO);

    int updateStatus(OrderDO orderDO);

    @Update("UPDATE c_order " +
            "SET " +
                "deleted_at = UNIX_TIMESTAMP( NOW( ) ) " +
            "WHERE " +
                "order_sn = #{orderSN}")
    int deleteByOrderSN(long orderSN);

    @Select("SELECT " +
                "id " +
            "FROM " +
                "c_order " +
            "WHERE " +
                "c_user_id = #{customerUserId} " +
                "AND order_sn = #{orderSN}")
    Long customerUserEquals(@Param("customerUserId") long customerUserId, @Param("orderSN") long orderSN);

    @Select("SELECT " +
                "order_sn, " +
                "c_user_id, " +
                "status " +
            "FROM " +
                "c_order " +
            "WHERE " +
                "order_sn = #{orderSN}")
    OrderDO selectOneByOrderSN(long orderSN);

    @Select("SELECT " +
            " count( * )  " +
            "FROM " +
            " `c_order`  " +
            "WHERE " +
            " c_user_id =#{userId}")
    long countOrderByUser(Long userId);
}
